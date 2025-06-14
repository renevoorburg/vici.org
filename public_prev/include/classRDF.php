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


class RDF
{

    private $subjectKind;
    private $pntId = null;
    private $obj;               // query results row
    private $images;
    private $lines;

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
        set_time_limit(60);
        $db = new DBConnector();

        $extraSql = ($this->pntId ? " AND pnt_id=" . $this->pntId : "");
        $sql = "SELECT pnt_id, pnt_name, pnt_dflt_short, pnt_lat, pnt_lng, pmeta_extids, pmeta_pleiades, pmeta_livius, pmeta_dare, pkind_name, pnt_visible, pmeta_loc_accuracy, pmeta_startyr, pmeta_endyr,
            LOCATE('<span>wikidata=', pmeta_extids) as wikidata,
            GROUP_CONCAT(if (psum_lang='de', `psum_short`, null)) as de_short,
            GROUP_CONCAT(if (psum_lang='en', `psum_short`, null)) as en_short,
            GROUP_CONCAT(if (psum_lang='fr', `psum_short`, null)) as fr_short,
            GROUP_CONCAT(if (psum_lang='nl', `psum_short`, null)) as nl_short,
            GROUP_CONCAT(if (psum_lang='de', `psum_pnt_name`, null)) as de_name,
            GROUP_CONCAT(if (psum_lang='en', `psum_pnt_name`, null)) as en_name,
            GROUP_CONCAT(if (psum_lang='fr', `psum_pnt_name`, null)) as fr_name,
            GROUP_CONCAT(if (psum_lang='nl', `psum_pnt_name`, null)) as nl_name
            FROM points
            LEFT JOIN pmetadata on pnt_id=pmeta_pnt_id
            LEFT JOIN psummaries on pnt_id=psum_pnt_id
            LEFT JOIN pkinds on pnt_kind=pkind_id
            WHERE pnt_hide=0 $extraSql GROUP BY pnt_id, pmeta_extids, pmeta_pleiades, pmeta_livius, pmeta_dare, pmeta_loc_accuracy, pmeta_startyr, pmeta_endyr";
        $result = $db->query($sql);

        $this->lines = new SiteLineData($this->pntId);
        $this->images = new ImageData($this->pntId);

        header('Content-type: application/rdf+xml');
        echo '<?xml version="1.0"?>', "\n";
        echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"', "\n";
        echo ' xmlns:dc="http://purl.org/dc/elements/1.1/"', "\n";
        echo ' xmlns:dcterms="http://purl.org/dc/terms/"', "\n";
        echo ' xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"', "\n";
        echo ' xmlns:gis="http://www.opengis.net/ont/geosparql#"', "\n";
        echo ' xmlns:lawd="http://lawd.info/ontology/"', "\n";
        echo ' xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"', "\n";
        echo ' xmlns:skos="http://www.w3.org/2004/02/skos/core#"', "\n";
        echo ' xmlns:vici="http://vici.org/ns/2015/07#"', "\n";
        echo ' xmlns:sf="http://www.opengis.net/ont/sf#"', "\n";
        echo ' xmlns:foaf="http://xmlns.com/foaf/0.1/"', "\n";
        echo ' xmlns:cc="http://creativecommons.org/ns#"', "\n";
        echo ' xmlns:owl="http://www.w3.org/2002/07/owl#">', "\n";


        // print marker records:
        if ($result) {
            while ($this->obj = $result->fetch_object()) {
                $this->printSite();
            }

            $result->close();
        }


        // print image records:
        while ($this->images->walk()) {
            $this->printImage();
        }

        echo '</rdf:RDF>';

    }

    private function printSite()
    {
        echo '<lawd:Place rdf:about="http://vici.org/vici/', $this->obj->pnt_id, '">', "\n";
        echo '  <rdf:type rdf:resource="http://vici.org/ns/2015/07#', ucfirst($this->obj->pkind_name), '"/>', "\n";
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
        echo '  <dc:description>', htmlspecialchars($this->obj->pnt_dflt_short), '</dc:description>', "\n";
        if ($this->obj->de_short) {
            echo '  <dc:description xml:lang="de">', htmlspecialchars($this->obj->de_short), '</dc:description>', "\n";
        }
        if ($this->obj->en_short) {
            echo '  <dc:description xml:lang="en">', htmlspecialchars($this->obj->en_short), '</dc:description>', "\n";
        }
        if ($this->obj->fr_short) {
            echo '  <dc:description xml:lang="fr">', htmlspecialchars($this->obj->fr_short), '</dc:description>', "\n";
        }
        if ($this->obj->nl_short) {
            echo '  <dc:description xml:lang="nl">', htmlspecialchars($this->obj->nl_short), '</dc:description>', "\n";
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

            if ($this->obj->pmeta_endyr> date("Y") ) {
                echo '  <dcterms:date>'.$this->obj->pmeta_startyr.'</dcterms:date>', "\n";
            } else {

                echo '  <dcterms:temporal>' . $this->obj->pmeta_startyr . '/' . $this->obj->pmeta_endyr . '</dcterms:temporal>', "\n";

            }
        }

        while ($this->images->walk($this->obj->pnt_id)) {
            echo '  <foaf:depiction rdf:resource="http://vici.org/image/' . $this->images->current()->getId() . '"/>', "\n";
        }


        // representative point:
        echo '    <geo:location>', "\n";
        echo '      <rdf:Description>', "\n";
        echo '        <geo:lat rdf:datatype="http://www.w3.org/2001/XMLSchema#double">', $this->obj->pnt_lat, '</geo:lat>', "\n";
        echo '        <geo:long rdf:datatype="http://www.w3.org/2001/XMLSchema#double">', $this->obj->pnt_lng, '</geo:long>', "\n";
        echo '        <vici:hasAccuracy>',$this->obj->pmeta_loc_accuracy,'</vici:hasAccuracy>',"\n";
        echo '      </rdf:Description>', "\n";
        echo '    </geo:location>', "\n";

        // add linedata:
        echo '  <gis:hasGeometry>', "\n";
        while ($this->lines->walk($this->obj->pnt_id)) {
            echo '    <sf:', $this->lines->current()->getOpengisLineKind(), '>', "\n";
            echo '      <gis:asWKT rdf:datatype="http://www.opengis.net/ont/geosparql#wktLiteral">', $this->lines->current()->getLineParts('wkt'), '</gis:asWKT>', "\n";

            echo '      <rdfs:isDefinedBy>', "\n";
            echo '        <foaf:Document>', "\n";
            if ($license = $this->lines->current()->getLicense()) { // TODO ugly, should be dealt with in getLicense()
                echo '          <cc:license rdf:resource="', $license, '"/>', "\n";
            } else {
                echo '          <dcterms:rights>All rights reserved.</dcterms:rights>', "\n";
            }
            if ($owner = $this->lines->current()->getOwner()) {
                echo '          <dc:creator>', htmlspecialchars($owner), '</dc:creator>', "\n";
            }
            echo '        </foaf:Document>', "\n";
            echo '      </rdfs:isDefinedBy>', "\n";
            echo '    </sf:', $this->lines->current()->getOpengisLineKind(), '>', "\n";
        }
        if ($this->lines->count() == 0) {
            echo '      <sf:Point>', "\n";
            echo '        <gis:asWKT rdf:datatype="http://www.opengis.net/ont/geosparql#wktLiteral">POINT (', $this->obj->pnt_lng, ' ', $this->obj->pnt_lat, ')</gis:asWKT>', "\n";
            echo '      </sf:Point>', "\n";
        }
        echo '  </gis:hasGeometry>', "\n";

        echo '  <rdfs:isDefinedBy rdf:resource="http://vici.org/vici/', $this->obj->pnt_id, '/rdf"/>', "\n";
        echo '  <foaf:isPrimaryTopicOf rdf:resource="http://vici.org/vici/', $this->obj->pnt_id, '/"/>', "\n";
        echo '</lawd:Place>', "\n";

        echo '<foaf:Document rdf:about="http://vici.org/vici/', $this->obj->pnt_id, '/rdf">', "\n";
        echo '  <foaf:primaryTopic rdf:resource="http://vici.org/vici/', $this->obj->pnt_id, '"/>', "\n";
        echo '  <cc:license rdf:resource="http://creativecommons.org/publicdomain/zero/1.0/"/>', "\n";
        echo '</foaf:Document>', "\n";

    }

    private function printImage()
    {
        echo '<foaf:image rdf:about="http://vici.org/image/', $this->images->current()->getId(), '">', "\n";
        echo '  <dc:title>', htmlspecialchars($this->images->current()->getTitle()), '</dc:title>', "\n";
        echo '  <dc:description>' . htmlspecialchars($this->images->current()->getDescription()) . '</dc:description>';
        if ($license = $this->images->current()->getLicense()) {
            echo '  <cc:license rdf:resource="', $license, '"/>', "\n";
        }
        echo '</foaf:image>', "\n";
    }

}
