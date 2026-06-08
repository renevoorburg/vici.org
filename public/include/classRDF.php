<?php

/**
 * RDF class outputs RDF for all data or a specific site.
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
    private $printedPersons = [];
    private $personBuffer = '';
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

        header('Content-type: text/turtle; charset=UTF-8');
        header('Content-Disposition: attachment; filename="vici.ttl"');
        echo '@prefix rdf:    <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .', "\n";
        echo '@prefix rdfs:   <http://www.w3.org/2000/01/rdf-schema#> .', "\n";
        echo '@prefix xsd:    <http://www.w3.org/2001/XMLSchema#> .', "\n";
        echo '@prefix skos:   <http://www.w3.org/2004/02/skos/core#> .', "\n";
        echo '@prefix gis:    <http://www.opengis.net/ont/geosparql#> .', "\n";
        echo '@prefix sf:     <http://www.opengis.net/ont/sf#> .', "\n";
        echo '@prefix sdo:    <https://schema.org/> .', "\n";
        echo '@prefix vici:   <http://vici.org/ns/2026/06/> .', "\n";
        echo "\n";

        // print site and image records in batches:
        foreach (array_chunk($pntIds, 500) as $batch) {
            $this->printBatch($db, $batch);
        }

        $this->printRootDataset();

    }

    private function printBatch($db, array $batch)
    {
        $ids = implode(',', $batch);
        $sql = "SELECT pnt_id, pnt_name, pnt_dflt_short, pnt_lat, pnt_lng, pmeta_extids, pmeta_pleiades, pmeta_livius, pmeta_dare, pkind_name, pnt_visible, pmeta_loc_accuracy, pmeta_startyr, pmeta_endyr, pmeta_create_date, pmeta_edit_date, creator.acc_id as metacreator_id, creator.acc_realname as metacreator_name, editor.acc_id as metaeditor_id, editor.acc_realname as metaeditor_name,
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
            GROUP_CONCAT(DISTINCT if (ptxt_lang='nl', `ptxt_full`, null)) as nl_text,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='de', ptxt_edit_date, null)) as de_edit_date,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='en', ptxt_edit_date, null)) as en_edit_date,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='fr', ptxt_edit_date, null)) as fr_edit_date,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='nl', ptxt_edit_date, null)) as nl_edit_date,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='de', de_editor.acc_realname, null)) as de_editor,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='en', en_editor.acc_realname, null)) as en_editor,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='fr', fr_editor.acc_realname, null)) as fr_editor,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='nl', nl_editor.acc_realname, null)) as nl_editor,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='de', de_editor.acc_id, null)) as de_editor_id,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='en', en_editor.acc_id, null)) as en_editor_id,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='fr', fr_editor.acc_id, null)) as fr_editor_id,
            GROUP_CONCAT(DISTINCT if (ptxt_lang='nl', nl_editor.acc_id, null)) as nl_editor_id
            FROM points
            LEFT JOIN pmetadata on pnt_id=pmeta_pnt_id
            LEFT JOIN psummaries on pnt_id=psum_pnt_id
            LEFT JOIN pkinds on pnt_kind=pkind_id
            LEFT JOIN ptexts on pnt_id=ptxt_pnt_id
            LEFT JOIN accounts as creator on pmeta_creator=creator.acc_id
            LEFT JOIN accounts as editor on pmeta_editor=editor.acc_id
            LEFT JOIN accounts as de_editor on (ptxt_lang='de' AND ptxt_editor=de_editor.acc_id)
            LEFT JOIN accounts as en_editor on (ptxt_lang='en' AND ptxt_editor=en_editor.acc_id)
            LEFT JOIN accounts as fr_editor on (ptxt_lang='fr' AND ptxt_editor=fr_editor.acc_id)
            LEFT JOIN accounts as nl_editor on (ptxt_lang='nl' AND ptxt_editor=nl_editor.acc_id)
            WHERE pnt_hide=0 AND pnt_id IN ($ids) GROUP BY pnt_id";
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

    private function printRootDataset()
    {
        echo '<http://vici.org/dataset>', "\n";
        echo '  a sdo:Dataset ;', "\n";
        echo '  sdo:name "Vici.org - archeologische atlas"@nl , "Vici.org - archaeological atlas"@en ;', "\n";
        echo '  sdo:identifier "http://vici.org/dataset" ;', "\n";
        echo '  sdo:inLanguage "nl", "en";', "\n";

        echo '  sdo:description "Vici.org is a community-driven archaeological atlas, primarily focused on Western Antiquity, including the Classical, Hellenistic, and Roman periods."@en ;', "\n";
        echo '  sdo:description "Vici.org is een door de gemeenschap gedreven archeologische atlas, voornamelijk gericht op de westerse oudheid, inclusief de klassieke, hellenistische en Romeinse perioden."@nl ;', "\n";
        echo '  sdo:url <https://data.digitopia.nl/uri:http://vici.org/dataset> ;', "\n";
        echo '  sdo:creator <http://vici.org/user/1> ;', "\n";
        echo '  sdo:publisher <http://vici.org/user/1> ;', "\n";
        echo '  sdo:dateCreated "2011-11-30"^^xsd:date ;', "\n";
        echo '  sdo:datePublished "2012-01-23"^^xsd:date ;', "\n";
        echo '  sdo:license <http://creativecommons.org/publicdomain/zero/1.0/> ;', "\n";
        echo '  rdfs:comment "Some nodes in this dataset are licensed differently. Check the RDF of CreativeWork entities for local licensing information."@en ;', "\n";
        echo '  rdfs:comment "Sommige eniteiten in deze dataset vallen onder een andere licentie. Controleer de RDF van CreativeWork-entiteiten op lokale licentie-informatie."@nl ;', "\n";

        echo '  sdo:temporalCoverage "-2000/1500" ;', "\n";
        echo '  sdo:about <http://www.wikidata.org/entity/Q1252>, <http://www.wikidata.org/entity/Q1253>, <http://www.wikidata.org/entity/Q1254>, <http://www.wikidata.org/entity/Q1255>, <http://www.wikidata.org/entity/Q831054>,  <http://www.wikidata.org/entity/Q21166>, <http://www.wikidata.org/entity/Q43229>, <http://www.wikidata.org/entity/Q831055>, <http://www.wikidata.org/entity/Q44613>, <http://www.wikidata.org/entity/Q178061> ;', "\n";
        echo '  sdo:keywords "cultural heritage, antiquity, prehistory, archaeological sites, artifacts, ancient settlements, burial sites, temples, roads, trade routes, history, museum, museums, Romans, Germans, Greeks, Celts, Egyptians"@en ;', "\n";
        echo '  sdo:keywords "cultureel erfgoed, oudheid, prehistorie, vroege middeleeuwen, archeologische vindplaatsen, artefacten, oude nederzettingen, begraafplaatsen, tempels, wegen, handelroutes, oude kaarten, geschiedenis, museum, musea, Romeinen, Germanen, Kelten, Grieken, Egyptenaren"@nl ;', "\n";
        echo '  sdo:spatialCoverage  <http://www.wikidata.org/enity/Q2>, <http://www.wikidata.org/entity/Q46>, <http://www.wikidata.org/entity/Q55>, <http://www.wikidata.org/entity/Q4918> ;', "\n";


        // echo '  rdfs:isDefinedBy <http://vici.org/dataset/rdf>', "\n";
        echo ".\n\n";
        // echo '<http://vici.org/dataset/rdf>', "\n";
        // echo '  a sdo:Dataset ;', "\n";
        // echo '  rdfs:label "Meta resource for http://vici.org/dataset"@en ;', "\n";
        // echo '  rdfs:label "Meta-resource voor http://vici.org/dataset"@nl ;', "\n";
        // echo '  sdo:about <http://vici.org/dataset> ;', "\n";
        // echo '  sdo:license <http://creativecommons.org/publicdomain/zero/1.0/>', "\n";
        // echo ".\n\n";
    }

    private function printSite()
    {
        $id = $this->obj->pnt_id;

        echo '<http://vici.org/vici/', $id, '>', "\n";
        echo '  a sdo:Place, vici:', ucfirst($this->obj->pkind_name), " ;\n";
        echo '  rdfs:label ', $this->ttlLiteral($this->obj->pnt_name), " ;\n";
        if ($this->obj->de_name) {
            echo '  rdfs:label ', $this->ttlLiteral($this->obj->de_name, 'de'), " ;\n";
        }
        if ($this->obj->en_name) {
            echo '  rdfs:label ', $this->ttlLiteral($this->obj->en_name, 'en'), " ;\n";
        }
        if ($this->obj->fr_name) {
            echo '  rdfs:label ', $this->ttlLiteral($this->obj->fr_name, 'fr'), " ;\n";
        }
        if ($this->obj->nl_name) {
            echo '  rdfs:label ', $this->ttlLiteral($this->obj->nl_name, 'nl'), " ;\n";
        }
        echo '  sdo:disambiguatingDescription ', $this->ttlLiteral($this->obj->pnt_dflt_short), " ;\n";
        if ($this->obj->de_short) {
            echo '  sdo:description ', $this->ttlLiteral($this->obj->de_short, 'de'), " ;\n";
        }
        if ($this->obj->en_short) {
            echo '  sdo:description ', $this->ttlLiteral($this->obj->en_short, 'en'), " ;\n";
        }
        if ($this->obj->fr_short) {
            echo '  sdo:description ', $this->ttlLiteral($this->obj->fr_short, 'fr'), " ;\n";
        }
        if ($this->obj->nl_short) {
            echo '  sdo:description ', $this->ttlLiteral($this->obj->nl_short, 'nl'), " ;\n";
        }
        echo '  vici:isVisible ', (int)$this->obj->pnt_visible, " ;\n";

        if ($this->obj->pmeta_pleiades) {
            echo '  skos:exactMatch <http://pleiades.stoa.org/places/', $this->obj->pmeta_pleiades, '> ;', "\n";
        }
        if ($this->obj->pmeta_livius) {
            $livius = preg_replace('/=/', '/', $this->obj->pmeta_livius);
            echo '  skos:exactMatch <http://www.livius.org/', $livius, '> ;', "\n";
        }
        if ($this->obj->pmeta_dare) {
            echo '  skos:exactMatch <http://dare.ht.lu.se/places/', $this->obj->pmeta_dare, '> ;', "\n";
        }
        $ref = new ExtIdRefs($this->obj->pmeta_extids);
        if ($ref->getWikidata()) {
            echo '  skos:exactMatch <http://www.wikidata.org/entity/', $ref->getWikidata(), '> ;', "\n";
        }

        if ($this->obj->pmeta_startyr) {
            if ($this->obj->pmeta_endyr > date("Y")) {
                echo '  sdo:startDate ', $this->ttlLiteral($this->obj->pmeta_startyr), " ;\n";
            } else {
                $endyr = $this->obj->pmeta_endyr ? $this->obj->pmeta_endyr : '..';
                echo '  sdo:temporalCoverage ', $this->ttlLiteral($this->obj->pmeta_startyr . '/' . $endyr), " ;\n";
            }
        }

        while ($this->images->walk($this->obj->pnt_id)) {
            echo '  sdo:image <http://vici.org/image/', $this->images->current()->getId(), '> ;', "\n";
        }

        // representative point:
        echo '  gis:hasGeometry [ a sf:Point ;', "\n";
        echo '    rdfs:label "Representative point"@en ;', "\n";
        echo '    gis:asWKT "POINT (', $this->obj->pnt_lng, ' ', $this->obj->pnt_lat, ')"^^gis:wktLiteral', "\n";
        echo '  ] ;', "\n";

        // line geometries:
        while ($this->lines->walk($this->obj->pnt_id)) {
            $kind = $this->lines->current()->getOpengisLineKind();
            echo '  gis:hasGeometry [ a sf:', $kind, " ;\n";
            echo '    rdfs:label "Structural geometry"@en ;', "\n";
            echo '    gis:asWKT ', $this->ttlLiteral($this->lines->current()->getLineParts('wkt'), null, 'gis:wktLiteral');
            if (($license = $this->lines->current()->getLicense()) && $license != 'http://creativecommons.org/publicdomain/zero/1.0/') {
                echo " ;\n    sdo:license <", $license, '>';
            }
            if ($owner = $this->lines->current()->getOwner()) {
                echo " ;\n    sdo:creator ", $this->ttlLiteral($owner);
            }
            if ($this->lines->current()->getUploaderId() && $this->lines->current()->getUploaderName()) {
                $personUri = $this->personUri($this->lines->current()->getUploaderId(), $this->lines->current()->getUploaderName());
                echo " ;\n    sdo:contributor <", $personUri, '>';
            }
            if ($attribution = $this->lines->current()->getAttribution()) {
                echo " ;\n    sdo:copyrightNotice ", $this->ttlLiteral($attribution, null, 'rdf:HTML');
            }
            if ($lineDate = $this->lines->current()->getDate()) {
                echo ' ;', "\n    sdo:dateCreated \"", substr($lineDate, 0, 10), '"^^xsd:date';
            }
            echo "\n  ] ;\n";
        }

        // extended text descriptions:
        $langNamesEn = ['nl' => 'Dutch', 'de' => 'German', 'fr' => 'French', 'en' => 'English'];
        $langNamesNl = ['nl' => 'Nederlandse', 'de' => 'Duitse', 'fr' => 'Franse', 'en' => 'Engelse'];
        foreach ($this->lngObjs as $lang => $lngObj) {
            $field = $lang . '_text';
            if ($this->obj->$field) {
                $text = ViciCommon::link_urls(ViciCommonLogic::parseAnnotation($this->obj->$field, $lngObj));
                $editorField = $lang . '_editor';
                $editDateField = $lang . '_edit_date';
                echo '  sdo:description [', "\n";
                echo '    a sdo:TextObject ;', "\n";
                echo '    rdfs:label "Extended ', $langNamesEn[$lang], ' description in HTML"@en ;', "\n";
                echo '    rdfs:label "Uitgebreide ', $langNamesNl[$lang], ' beschrijving in HTML"@nl ;', "\n";
                echo '    sdo:inLanguage "', $lang, '" ;', "\n";
                echo '    sdo:text ', $this->ttlLiteral($text, $lang, 'rdf:HTML'), " ;\n";
                $editorIdField = $lang . '_editor_id';
                if ($this->obj->$editorField && $this->obj->$editorIdField) {
                    $personUri = $this->personUri($this->obj->$editorIdField, $this->obj->$editorField);
                    echo '    sdo:contributor <', $personUri, '> ;', "\n";
                }
                if ($this->obj->$editDateField) {
                    echo '    sdo:dateModified "', substr($this->obj->$editDateField, 0, 10), '"^^xsd:date ;', "\n";
                }
                echo '    sdo:license <http://creativecommons.org/licenses/by-sa/3.0/>', "\n";
                echo '  ] ;', "\n";
            }
        }

        echo '  rdfs:isDefinedBy <http://vici.org/vici/', $id, '/rdf> ;', "\n";
        echo '  sdo:mainEntityOfPage <https://vici.org/vici/', $id, '/>', "\n";
        echo ".\n\n";
        $this->flushPersons();

        // Dataset:
        echo '<http://vici.org/vici/', $id, '/rdf>', "\n";
        echo '  a sdo:Dataset ;', "\n";
        echo '  sdo:about <http://vici.org/vici/', $id, '> ;', "\n";
        echo '  rdfs:label "Meta resource for http://vici.org/vici/', $id, '"@en ;', "\n";
        echo '  rdfs:label "Meta-resource voor http://vici.org/vici/', $id, '"@nl ;', "\n";
        echo '  rdfs:comment "Anonymous subnodes of the target resource may be licensed differently. Check the RDF of CreativeWork entities for local licensing information."@en ;', "\n";
        echo '  rdfs:comment "Anonieme subnodes van de doel-resource kunnen onder een andere licentie vallen. Controleer de RDF van CreativeWork-entiteiten op lokale licentie-informatie."@nl ;', "\n";
        if ($this->obj->pmeta_create_date) {
            echo '  sdo:dateCreated "', substr($this->obj->pmeta_create_date, 0, 10), '"^^xsd:date ;', "\n";
        }
        if ($this->obj->pmeta_edit_date) {
            echo '  sdo:dateModified "', substr($this->obj->pmeta_edit_date, 0, 10), '"^^xsd:date ;', "\n";
        }
        if ($this->obj->metacreator_name && $this->obj->metacreator_id) {
            $personUri = $this->personUri($this->obj->metacreator_id, $this->obj->metacreator_name);
            echo '  sdo:creator <', $personUri, '> ;', "\n";
        }
        if ($this->obj->metaeditor_name && $this->obj->metaeditor_id && $this->obj->metaeditor_id !== $this->obj->metacreator_id) {
            $personUri = $this->personUri($this->obj->metaeditor_id, $this->obj->metaeditor_name);
            echo '  sdo:editor <', $personUri, '> ;', "\n";
        }
        echo '  sdo:isPartOf <http://vici.org/dataset> ;', "\n";
        echo '  sdo:license <http://creativecommons.org/publicdomain/zero/1.0/>', "\n";
        echo ".\n\n";
        $this->flushPersons();
    }

    private function printImage()
    {
        $img = $this->images->current();
        echo '<http://vici.org/image/', $img->getId(), '>', "\n";
        echo '  a sdo:ImageObject ;', "\n";
        echo '  rdfs:isDefinedBy <http://vici.org/image/', $img->getId(), '/rdf> ;', "\n";
        echo '  sdo:name ', $this->ttlLiteral($img->getTitle()), " ;\n";
        if ($description = $img->getDescription()) {
            echo '  sdo:description ', $this->ttlLiteral($description), " ;\n";
        }
        echo '  sdo:contentUrl <', $this->sanitizeUri('https://images.vici.org/auto' . $img->getPath()), '> ;', "\n";
        echo '  sdo:thumbnailUrl <', $this->sanitizeUri('https://images.vici.org/size/h200' . $img->getPath()), '> ;', "\n";
        if ($license = $img->getLicense()) {
            echo '  sdo:license <', $this->sanitizeUri($license), '> ;', "\n";
        }
        if ($date = $img->getDate()) {
            echo '  sdo:uploadDate "', substr($date, 0, 10), '"^^xsd:date ;', "\n";
        }
        if (!$img->isOwnWork() && $source = $img->getSource()) {
            if (preg_match('#^https?://#i', $source)) {
                echo '  sdo:isBasedOn <', $this->sanitizeUri($source), '> ;', "\n";
            } else {
                echo '  sdo:isBasedOn ', $this->ttlLiteral($source), " ;\n";
            }
        }
        if ($img->isOwnWork() && $img->getUploaderName() && $img->getUploaderId()) {
            $personUri = $this->personUri($img->getUploaderId(), $img->getUploaderName());
            echo '  sdo:creator <', $personUri, '>', "\n";
        } else {
            $last = '';
            if ($creator = $img->getCreatorName()) {
                $last = '  sdo:copyrightNotice ' . $this->ttlLiteral('© ' . $creator);
            }
            if ($img->getUploaderName() && $img->getUploaderId()) {
                if ($last) {
                    echo $last, " ;\n";
                }
                $personUri = $this->personUri($img->getUploaderId(), $img->getUploaderName());
                $last = '  sdo:contributor <' . $personUri . '>';
            }
            echo $last, "\n";
        }
        echo ".\n\n";
        echo '<http://vici.org/image/', $img->getId(), '/rdf>', "\n";
        echo '  a sdo:Dataset ;', "\n";
        echo '  rdfs:label "Meta resource for http://vici.org/image/', $img->getId(), '"@en ;', "\n";
        echo '  rdfs:label "Meta-resource voor http://vici.org/image/', $img->getId(), '"@nl ;', "\n";
        echo '  sdo:about <http://vici.org/image/', $img->getId(), '> ;', "\n";
        echo '  sdo:isPartOf <http://vici.org/dataset> ;', "\n";
        echo '  sdo:license <', $this->sanitizeUri($img->getLicense() ?: 'http://creativecommons.org/publicdomain/zero/1.0/'), '>', "\n";
        echo ".\n\n";
        $this->flushPersons();
    }

    private function personUri($id, $name)
    {
        $uri = 'http://vici.org/user/' . (int)$id;
        if (!isset($this->printedPersons[$uri])) {
            $this->printedPersons[$uri] = true;
            $this->personBuffer .= '<' . $uri . '>' . "\n";
            $this->personBuffer .= '  a sdo:Person ;' . "\n";
            $this->personBuffer .= '  rdfs:comment "The real or fictious name of a user registered at Vici.org."@en ;' . "\n";
            $this->personBuffer .= '  rdfs:comment "Echte of fictieve naam van een op vici.org geregistreerde gebruiker."@nl ;' . "\n";
            $this->personBuffer .= '  sdo:name ' . $this->ttlLiteral($name) . ' ;' . "\n";
            $this->personBuffer .= '  rdfs:isDefinedBy <' . $uri . '/rdf> .' . "\n\n";
            $this->personBuffer .= '<' . $uri . '/rdf>' . "\n";
            $this->personBuffer .= '  a sdo:Dataset ;' . "\n";
            $this->personBuffer .= '  rdfs:label "Meta resource for ' . $uri . '"@en ;' . "\n";
            $this->personBuffer .= '  rdfs:label "Meta-resource voor ' . $uri . '"@nl ;' . "\n";
            $this->personBuffer .= '  sdo:about <' . $uri . '> ;' . "\n";
            $this->personBuffer .= '  sdo:isPartOf <http://vici.org/dataset> ;' . "\n";
            $this->personBuffer .= '  sdo:license <http://creativecommons.org/publicdomain/zero/1.0/> .' . "\n\n";
        }
        return $uri;
    }

    private function flushPersons()
    {
        if ($this->personBuffer !== '') {
            echo $this->personBuffer;
            $this->personBuffer = '';
        }
    }

    private function sanitizeUri($uri)
    {
        return str_replace(
            ['\\', ' ', '"', '<', '>', '{', '}', '|', '^', '`'],
            ['%5C', '%20', '%22', '%3C', '%3E', '%7B', '%7D', '%7C', '%5E', '%60'],
            $uri
        );
    }

    private function ttlLiteral($value, $lang = null, $datatype = null)
    {
        $escaped = str_replace(['\\', '"', "\n", "\r", "\t"], ['\\\\', '\\"', '\\n', '\\r', '\\t'], $value);
        if ($datatype) {
            $tripleEscaped = str_replace(['\\', '"""'], ['\\\\', '\\"\\"\\"'], $value);
            return '"""' . $tripleEscaped . '"""^^' . $datatype;
        }
        if ($lang) {
            return '"' . $escaped . '"@' . $lang;
        }
        return '"' . $escaped . '"';
    }

}
