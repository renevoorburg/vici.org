<?php

/**
 * RDF class outputs RDF for all data or a specific site.
 *
 *
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.2.2 - 2015-09-16
 */

require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../include/classDBConnector.php';
require_once __DIR__ . '/../include/classExtIdRefs.php';
require_once __DIR__ . '/../include/classSiteLineData.php';
require_once __DIR__ . '/../include/classImageData.php';
require_once __DIR__ . '/../include/classViciCommonLogic.php';
require_once __DIR__ . '/../include/classViciCommon.php';
require_once __DIR__ . '/../include/classLang.php';


class RDF
{

    private $subjectKind;
    private $pntId = null;
    private $obj;               // query results row
    private $images;
    private $lines;
    private $printedImages = [];
    private $lngObjs = [];

    /**
     * @param string $subjectKind For future use, currently only 'site's are serialised.
     * @param null $idStr Item to serialise, null for all.
     * @param string $format Format to serialise to.
     */
    public function __construct($subjectKind = 'site', $idStr = null)
    {
        // determine request
        $idArr = explode("/", $idStr);
        $this->pntId = ($idArr[0] != 'all' ? (integer)$idArr[0] : null);

        // load marker data:
        set_time_limit(180);
        foreach (['de', 'en', 'fr', 'nl'] as $lang) {
            $_GET['lang'] = $lang;
            $this->lngObjs[$lang] = new Lang();
        }
        $db = new DBConnector();

        if ($this->pntId) {
            $pntIds = [$this->pntId];
        } else {
            $pntIds = [];
            $result = $db->query("SELECT pnt_id FROM points WHERE pnt_hide=0 ORDER BY pnt_id");
            while ($row = $result->fetch_object()) {
                $pntIds[] = $row->pnt_id;
            }
            $result->close();
        }

        header('Content-type: application/rdf+xml');
        echo '<?xml version="1.0"?>', "\n";
        echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"', "\n";
        echo ' xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"', "\n";
        echo ' xmlns:gis="http://www.opengis.net/ont/geosparql#"', "\n";
        echo ' xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"', "\n";
        echo ' xmlns:skos="http://www.w3.org/2004/02/skos/core#"', "\n";
        echo ' xmlns:vici="http://vici.org/ns/2026/06/"', "\n";
        echo ' xmlns:sf="http://www.opengis.net/ont/sf#"', "\n";
        echo ' xmlns:schema="http://schema.org/">', "\n";


        // print site and image records in batches:
        foreach (array_chunk($pntIds, 100) as $batch) {
            $this->printBatch($db, $batch);
        }

        echo '</rdf:RDF>';

    }

    private function printBatch($db, array $batch)
    {
        $ids = implode(',', $batch);
        $sql = "SELECT pnt_id, pnt_name, pnt_dflt_short, pnt_lat, pnt_lng, pmeta_extids, pmeta_pleiades, pmeta_livius, pmeta_dare, pkind_name, pnt_visible, pmeta_loc_accuracy, pmeta_startyr, pmeta_endyr,
            LOCATE('<span>wikidata=', pmeta_extids) as wikidata,
            GROUP_CONCAT(DISTINCT if (psum_lang='de', `psum_short`, null)) as de_short,
            GROUP_CONCAT(DISTINCT if (psum_lang='en', `psum_short`, null)) as en_short,
            GROUP_CONCAT(DISTINCT if (psum_lang='fr', `psum_short`, null)) as fr_short,
            GROUP_CONCAT(DISTINCT if (psum_lang='nl', `psum_short`, null)) as nl_short,
            GROUP_CONCAT(DISTINCT if (psum_lang='de', `psum_pnt_name`, null)) as de_name,
            GROUP_CONCAT(DISTINCT if (psum_lang='en', `psum_pnt_name`, null)) as en_name,
            GROUP_CONCAT(DISTINCT if (psum_lang='fr', `psum_pnt_name`, null)) as fr_name,
            GROUP_CONCAT(DISTINCT if (psum_lang='nl', `psum_pnt_name`, null)) as nl_name,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='de', `ptxt_full`, null)) as de_text,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='en', `ptxt_full`, null)) as en_text,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='fr', `ptxt_full`, null)) as fr_text,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='nl', `ptxt_full`, null)) as nl_text
            FROM points
            LEFT JOIN pmetadata on pnt_id=pmeta_pnt_id
            LEFT JOIN psummaries on pnt_id=psum_pnt_id
            LEFT JOIN pkinds on pnt_kind=pkind_id
            LEFT JOIN ptexts on pnt_id=ptxt_pnt_id
            WHERE pnt_hide=0 AND pnt_id IN ($ids) GROUP BY pnt_id, pmeta_extids, pmeta_pleiades, pmeta_livius, pmeta_dare, pmeta_loc_accuracy, pmeta_startyr, pmeta_endyr";
        $result = $db->query($sql);
        if (!$result) {
            return;
        }

        while ($this->obj = $result->fetch_object()) {
            $this->lines = new SiteLineData($this->obj->pnt_id);
            $this->images = new ImageData($this->obj->pnt_id);

            $this->printSite();

            // print image records for this site:
            while ($this->images->walk()) {
                $imgId = $this->images->current()->getId();
                if (!isset($this->printedImages[$imgId])) {
                    $this->printedImages[$imgId] = true;
                    $this->printImage();
                }
            }
        }
        $result->close();
    }

    private function printSite()
    {
        echo '<schema:Place rdf:about="http://vici.org/vici/', $this->obj->pnt_id, '">', "\n";
        echo '  <rdf:type rdf:resource="http://vici.org/ns/2026/06/', ucfirst($this->obj->pkind_name), '"/>', "\n";
        echo '  <rdfs:label>', htmlspecialchars($this->obj->pnt_name), '</rdfs:label>' . "\n";
        if ($this->obj->de_name) {
            echo '  <rdfs:label xml:lang="de">', htmlspecialchars($this->obj->de_name), '</rdfs:label>', "\n";
        }
        if ($this->obj->en_name) {
            echo '  <rdfs:label xml:lang="en">', htmlspecialchars($this->obj->en_name), '</rdfs:label>', "\n";
        }
        if ($this->obj->fr_name) {
            echo '  <rdfs:label xml:lang="fr">', htmlspecialchars($this->obj->fr_name), '</rdfs:label>', "\n";
        }
        if ($this->obj->nl_name) {
            echo '  <rdfs:label xml:lang="nl">', htmlspecialchars($this->obj->nl_name), '</rdfs:label>', "\n";
        }
        echo '  <schema:disambiguatingDescription>', htmlspecialchars($this->obj->pnt_dflt_short), '</schema:disambiguatingDescription>', "\n";
        if ($this->obj->de_short) {
            echo '  <schema:description xml:lang="de">', htmlspecialchars($this->obj->de_short), '</schema:description>', "\n";
        }
        if ($this->obj->en_short) {
            echo '  <schema:description xml:lang="en">', htmlspecialchars($this->obj->en_short), '</schema:description>', "\n";
        }
        if ($this->obj->fr_short) {
            echo '  <schema:description xml:lang="fr">', htmlspecialchars($this->obj->fr_short), '</schema:description>', "\n";
        }
        if ($this->obj->nl_short) {
            echo '  <schema:description xml:lang="nl">', htmlspecialchars($this->obj->nl_short), '</schema:description>', "\n";
        }
        echo '  <vici:isVisible>', $this->obj->pnt_visible, '</vici:isVisible>', "\n";

        if ($this->obj->pmeta_pleiades) {
            echo '  <skos:exactMatch rdf:resource="http://pleiades.stoa.org/places/' . $this->obj->pmeta_pleiades . '"/>', "\n";
        }
        if ($this->obj->pmeta_livius) {
            $livius = preg_replace('/=/', '/', $this->obj->pmeta_livius);
            echo '  <skos:exactMatch rdf:resource="http://www.livius.org/' . $livius . '"/>', "\n";
        }
        if ($this->obj->pmeta_dare) {
            echo '  <skos:exactMatch rdf:resource="http://dare.ht.lu.se/places/' . $this->obj->pmeta_dare . '"/>', "\n";
        }
        if ($this->obj->wikidata) {
            $ref = new ExtIdRefs($this->obj->pmeta_extids);
            echo '  <skos:exactMatch rdf:resource="http://www.wikidata.org/entity/' . $ref->getWikidata() . '"/>', "\n";
        }

        if ($this->obj->pmeta_startyr) {

            if ($this->obj->pmeta_endyr > date("Y")) {
                echo '  <schema:startDate>', $this->obj->pmeta_startyr, '</schema:startDate>', "\n";
            } else {
                echo '  <schema:temporalCoverage>', $this->obj->pmeta_startyr, '/', $this->obj->pmeta_endyr, '</schema:temporalCoverage>', "\n";
            }
        }

        while ($this->images->walk($this->obj->pnt_id)) {
            echo '  <schema:image rdf:resource="http://vici.org/image/' . $this->images->current()->getId() . '"/>', "\n";
        }


        // add representative point and linedata:
        echo '  <gis:hasGeometry>', "\n";
        echo '    <sf:Point>', "\n";
        echo '      <rdfs:label xml:lang="en">Representative point</rdfs:label>', "\n";
        echo '      <gis:asWKT rdf:datatype="http://www.opengis.net/ont/geosparql#wktLiteral">POINT (', $this->obj->pnt_lng, ' ', $this->obj->pnt_lat, ')</gis:asWKT>', "\n";
        echo '    </sf:Point>', "\n";
        echo '  </gis:hasGeometry>', "\n";
        while ($this->lines->walk($this->obj->pnt_id)) {
            echo '  <gis:hasGeometry>', "\n";
            echo '    <sf:', $this->lines->current()->getOpengisLineKind(), '>', "\n";
            echo '      <rdfs:label xml:lang="en">Structural geometry</rdfs:label>', "\n";
            echo '      <gis:asWKT rdf:datatype="http://www.opengis.net/ont/geosparql#wktLiteral">', $this->lines->current()->getLineParts('wkt'), '</gis:asWKT>', "\n";
            if (($license = $this->lines->current()->getLicense()) && $license != 'http://creativecommons.org/publicdomain/zero/1.0/') {
                echo '      <schema:license rdf:resource="', $license, '"/>', "\n";
            }
            if ($owner = $this->lines->current()->getOwner()) {
                echo '      <schema:creator>', htmlspecialchars($owner), '</schema:creator>', "\n";
            }
            echo '    </sf:', $this->lines->current()->getOpengisLineKind(), '>', "\n";
            echo '  </gis:hasGeometry>', "\n";
        }
        






        foreach ($this->lngObjs as $lang => $lngObj) {
            $field = $lang . '_text';
            if ($this->obj->$field) {
                $text = ViciCommon::link_urls(ViciCommonLogic::parseAnnotation($this->obj->$field, $lngObj));
                echo '  <schema:description>', "\n";
                echo '    <rdf:Description>', "\n";
                echo '      <rdf:type rdf:resource="http://schema.org/TextObject"/>', "\n";
                echo '      <schema:text xml:lang="', $lang, '"><![CDATA[', $text, ']]></schema:text>', "\n";
                echo '      <schema:license rdf:resource="http://creativecommons.org/licenses/by-sa/3.0/"/>', "\n";
                echo '    </rdf:Description>', "\n";
                echo '  </schema:description>', "\n";
            }
        }


        # http://vici.local/vici/49/rdf


 


        echo '  <rdfs:isDefinedBy rdf:resource="http://vici.org/vici/', $this->obj->pnt_id, '/rdf"/>', "\n";
        echo '  <schema:mainEntityOfPage rdf:resource="https://vici.org/vici/', $this->obj->pnt_id, '/"/>', "\n";




        echo '</schema:Place>', "\n";

        echo '<schema:Dataset rdf:about="http://vici.org/vici/', $this->obj->pnt_id, '/rdf">', "\n";
        echo '  <schema:about rdf:resource="http://vici.org/vici/', $this->obj->pnt_id, '"/>', "\n";
        echo '  <schema:license rdf:resource="http://creativecommons.org/publicdomain/zero/1.0/"/>', "\n";
        echo '</schema:Dataset>', "\n";



    }

    private function printImage()
    {
        echo '<schema:ImageObject rdf:about="http://vici.org/image/', $this->images->current()->getId(), '">', "\n";
        echo '  <schema:name>', htmlspecialchars($this->images->current()->getTitle()), '</schema:name>', "\n";
        $description = $this->images->current()->getDescription();
        if (!empty($description)) {
            echo '  <schema:description>', htmlspecialchars($description), '</schema:description>', "\n";
        }
        echo '  <schema:contentUrl rdf:resource="https://images.vici.org/auto', $this->images->current()->getPath(), '"/>', "\n";
        echo '  <schema:thumbnailUrl rdf:resource="https://images.vici.org/size/h200', $this->images->current()->getPath(), '"/>', "\n";
        if ($license = $this->images->current()->getLicense()) {
            echo '  <schema:license rdf:resource="', $license, '"/>', "\n";
        }
        echo '</schema:ImageObject>', "\n";
    }

}
