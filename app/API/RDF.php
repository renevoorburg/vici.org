<?php

namespace Vici\API;

use geoPHP;
use Vici\Session\Session;
use Vici\DB\DBConnector;
use Vici\I18n\Translator;
use Vici\Model\Site\Site;
use Vici\Model\Site\SiteRepository;
use Vici\Model\Site\Image\Image;
use Vici\Geometries\Line as GeoLine;

/**
 * Serialises sites to Turtle, same output as the legacy classRDF.
 * Handles a single site (/vici/{id}/rdf) or the full dataset (/vici/all/rdf).
 */
class RDF extends APICall
{
    private Session $session;
    private DBConnector $db;
    private SiteRepository $siteRepo;

    private array $printedImages = [];
    private array $printedPersons = [];
    private string $personBuffer = '';
    private array $translators = [];

    public function __construct(Session $session)
    {
        parent::__construct($session);
        $this->session = $session;
        $this->db = $session->getDBConnector();
        $this->siteRepo = new SiteRepository($this->db);
    }

    public function headers()
    {
        header('Content-type: text/turtle; charset=UTF-8');
        header('Content-Disposition: attachment; filename="vici.ttl"');
    }

    public function payload()
    {
        set_time_limit(3600);
        ini_set('memory_limit', '512M');

        foreach (['de', 'en', 'fr', 'nl'] as $lang) {
            $this->translators[$lang] = new Translator($lang);
        }

        $idStr = $this->session->getRequestedItemRaw();
        if ($idStr === 'all') {
            $stmt = $this->db->query("SELECT pnt_id FROM points WHERE pnt_hide=0 ORDER BY pnt_id");
            $pntIds = $stmt ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];
        } else {
            $pntIds = [(int)$idStr];
        }

        $this->printPrefixes();

        foreach (array_chunk($pntIds, 500) as $batch) {
            $this->printBatch($batch);
        }

        $this->printRootDataset();
    }

    private function printPrefixes(): void
    {
        echo '@prefix rdf:    <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .', "\n";
        echo '@prefix rdfs:   <http://www.w3.org/2000/01/rdf-schema#> .', "\n";
        echo '@prefix xsd:    <http://www.w3.org/2001/XMLSchema#> .', "\n";
        echo '@prefix skos:   <http://www.w3.org/2004/02/skos/core#> .', "\n";
        echo '@prefix gis:    <http://www.opengis.net/ont/geosparql#> .', "\n";
        echo '@prefix sf:     <http://www.opengis.net/ont/sf#> .', "\n";
        echo '@prefix sdo:    <https://schema.org/> .', "\n";
        echo '@prefix vici:   <http://vici.org/ns/2026/06/> .', "\n";
        echo "\n";
    }

    private function printBatch(array $batch): void
    {
        foreach ($batch as $pntId) {
            $site = $this->siteRepo->findById((int)$pntId);
            if (!$site || $site->isPublished) {
                continue;
            }

            $this->printSite($site);

            foreach ($site->images as $image) {
                if (!isset($this->printedImages[$image->id])) {
                    $this->printedImages[$image->id] = true;
                    $this->printImage($image);
                }
            }
        }
    }

    private function printRootDataset(): void
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
        echo '  sdo:dateModified "', date('Y-m-d'), '"^^xsd:date ;', "\n";
        echo '  sdo:license <http://creativecommons.org/publicdomain/zero/1.0/> ;', "\n";
        echo '  rdfs:comment "Some nodes in this dataset are licensed differently. Check the RDF of CreativeWork entities for local licensing information."@en ;', "\n";
        echo '  rdfs:comment "Sommige eniteiten in deze dataset vallen onder een andere licentie. Controleer de RDF van CreativeWork-entiteiten op lokale licentie-informatie."@nl ;', "\n";

        echo '  sdo:temporalCoverage "-2000/1500" ;', "\n";
        echo '  sdo:about <http://www.wikidata.org/entity/Q1669589>, <http://www.wikidata.org/entity/Q1747689>, <http://vocab.getty.edu/aat/300054328>, <https://data.cultureelerfgoed.nl/term/id/cht/05d4bfa9-4afd-49b6-a700-2ee517fa1857>, <http://vocab.getty.edu/aat/300387245>, <http://vocab.getty.edu/aat/300054331>  ;', "\n";
        echo '  sdo:keywords "cultural heritage, antiquity, prehistory, archaeological sites, artifacts, ancient settlements, burial sites, temples, roads, trade routes, history, museum, museums, Romans, Germans, Greeks, Celts, Egyptians"@en ;', "\n";
        echo '  sdo:keywords "cultureel erfgoed, oudheid, prehistorie, vroege middeleeuwen, archeologische vindplaatsen, artefacten, oude nederzettingen, begraafplaatsen, tempels, wegen, handelroutes, oude kaarten, geschiedenis, museum, musea, Romeinen, Germanen, Kelten, Grieken, Egyptenaren"@nl ;', "\n";
        echo '  sdo:spatialCoverage  <http://www.wikidata.org/entity/Q2>, <http://www.wikidata.org/entity/Q46>, <http://www.wikidata.org/entity/Q55>, <http://www.wikidata.org/entity/Q4918> ;', "\n";

        echo '  sdo:distribution [', "\n";
        echo '      a sdo:DataDownload ; ', "\n";
        echo '      sdo:contentUrl <https://data.digitopia.nl/sparql> ; ', "\n";
        echo '      sdo:dateModified "', date('Y-m-d'), '"^^xsd:date ;', "\n";
        echo '      sdo:description "sparql endpoint"^^xsd:string ; ', "\n";
        echo '      sdo:usageInfo <https://www.w3.org/TR/sparql11-protocol/> ; ', "\n";
        echo '      sdo:inLanguage "nl", "en"; ', "\n";
        echo '      sdo:license <https://creativecommons.org/publicdomain/zero/1.0/> ', "\n";
        echo '      ] .', "\n\n";
    }

    private function printSite(Site $site): void
    {
        $id = $site->id;

        echo '<http://vici.org/vici/', $id, '>', "\n";
        echo '  a sdo:Place, vici:', ucfirst($site->type->title), " ;\n";
        echo '  rdfs:label ', $this->ttlLiteral($site->defaultTitle), " ;\n";
        foreach ($site->locales as $lang => $locale) {
            if (!empty($locale->translatedTitle)) {
                echo '  rdfs:label ', $this->ttlLiteral($locale->translatedTitle, $lang), " ;\n";
            }
        }
        echo '  sdo:disambiguatingDescription ', $this->ttlLiteral($site->defaultSummary), " ;\n";
        foreach ($site->locales as $lang => $locale) {
            if (!empty($locale->translatedSummary)) {
                echo '  sdo:description ', $this->ttlLiteral($locale->translatedSummary, $lang), " ;\n";
            }
        }
        echo '  vici:isVisible ', (int)$site->isVisible, " ;\n";

        foreach ($site->identifiers as $identifier) {
            echo '  skos:exactMatch <', $this->sanitizeUri($identifier->uri), '> ;', "\n";
        }

        if ($site->period->startYear) {
            if ($site->period->endYear > date("Y")) {
                echo '  sdo:startDate ', $this->ttlLiteral($site->period->startQualifier ?: (string)$site->period->startYear), " ;\n";
            } else {
                $endyr = $site->period->endYear ? $site->period->endYear : '..';
                echo '  sdo:temporalCoverage ', $this->ttlLiteral($site->period->startYear . '/' . $endyr), " ;\n";
            }
        }

        foreach ($site->images as $image) {
            echo '  sdo:image <http://vici.org/image/', $image->id, '> ;', "\n";
        }

        // representative point:
        echo '  gis:hasGeometry [ a sf:Point ;', "\n";
        echo '    rdfs:label "Representative point"@en ;', "\n";
        echo '    gis:asWKT "POINT (', $site->representativeLocation->longitude, ' ', $site->representativeLocation->latitude, ')"^^gis:wktLiteral', "\n";
        echo '  ] ;', "\n";

        // structural geometry (all lines of the site form one geometry, as in the legacy serialiser):
        if (count($site->lines) > 0) {
            $this->printSiteGeometry($site);
        }

        // extended text descriptions:
        $langNamesEn = ['nl' => 'Dutch', 'de' => 'German', 'fr' => 'French', 'en' => 'English'];
        $langNamesNl = ['nl' => 'Nederlandse', 'de' => 'Duitse', 'fr' => 'Franse', 'en' => 'Engelse'];
        foreach ($site->locales as $lang => $locale) {
            if (empty($locale->description)) {
                continue;
            }
            $text = self::link_urls($this->parseAnnotation($locale->description, $lang));
            echo '  sdo:description [', "\n";
            echo '    a sdo:TextObject ;', "\n";
            echo '    rdfs:label "Extended ', $langNamesEn[$lang] ?? $lang, ' description in HTML"@en ;', "\n";
            echo '    rdfs:label "Uitgebreide ', $langNamesNl[$lang] ?? $lang, ' beschrijving in HTML"@nl ;', "\n";
            echo '    sdo:inLanguage "', $lang, '" ;', "\n";
            echo '    sdo:text ', $this->ttlLiteral($text, $lang, 'rdf:HTML'), " ;\n";
            if ($locale->editorName && $locale->editorId) {
                $personUri = $this->personUri($locale->editorId, $locale->editorName);
                echo '    sdo:contributor <', $personUri, '> ;', "\n";
            }
            if ($locale->editDate) {
                echo '    sdo:dateModified "', substr($locale->editDate, 0, 10), '"^^xsd:date ;', "\n";
            }
            echo '    sdo:license <http://creativecommons.org/licenses/by-sa/3.0/>', "\n";
            echo '  ] ;', "\n";
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
        if ($site->createDate) {
            echo '  sdo:dateCreated "', substr($site->createDate, 0, 10), '"^^xsd:date ;', "\n";
        }
        if ($site->updateDate) {
            echo '  sdo:dateModified "', substr($site->updateDate, 0, 10), '"^^xsd:date ;', "\n";
        }
        if ($site->creator && $site->creator->getRealName()) {
            $personUri = $this->personUri($site->creator->getId(), $site->creator->getRealName());
            echo '  sdo:creator <', $personUri, '> ;', "\n";
        }
        if ($site->updater && $site->updater->getRealName() && $site->updater->getId() !== $site->creator->getId()) {
            $personUri = $this->personUri($site->updater->getId(), $site->updater->getRealName());
            echo '  sdo:editor <', $personUri, '> ;', "\n";
        }
        echo '  sdo:isPartOf <http://vici.org/dataset> ;', "\n";
        echo '  sdo:license <http://creativecommons.org/publicdomain/zero/1.0/>', "\n";
        echo ".\n\n";
        $this->flushPersons();
    }

    private function printSiteGeometry(Site $site): void
    {
        // pldata_points is stored as [lat, lng]; WKT needs lng lat
        $parts = [];
        foreach ($site->lines as $line) {
            $coords = [];
            foreach ((array)$line->coordinates as $point) {
                $coords[] = $point[1] . ' ' . $point[0];
            }
            if ($coords) {
                $parts[] = '(' . implode(', ', $coords) . ')';
            }
        }
        if (!$parts) {
            return;
        }

        if (count($parts) === 1) {
            $kind = 'LineString';
            $wkt = 'LINESTRING ' . $parts[0];
        } else {
            $kind = 'MultiLineString';
            $wkt = 'MULTILINESTRING (' . implode(', ', $parts) . ')';
        }

        // the legacy serialiser takes uploader/license/owner/attribution/date of the first line
        $first = $site->lines[0];

        echo '  gis:hasGeometry [ a sf:', $kind, " ;\n";
        echo '    rdfs:label "Structural geometry"@en ;', "\n";
        echo '    gis:asWKT ', $this->ttlLiteral($wkt, null, 'gis:wktLiteral');
        $licenseUri = isset($site->lines->license) ? ($site->lines->license->uri ?? null) : null;
        if ($licenseUri && $licenseUri !== 'http://creativecommons.org/publicdomain/zero/1.0/') {
            echo " ;\n    sdo:license <", $this->sanitizeUri($licenseUri), '>';
        }
        if ($first->owner) {
            echo " ;\n    sdo:creator ", $this->ttlLiteral($first->owner);
        }
        if (isset($site->lines->uploader) && $site->lines->uploader->getId() && $site->lines->uploader->getRealName()) {
            $personUri = $this->personUri($site->lines->uploader->getId(), $site->lines->uploader->getRealName());
            echo " ;\n    sdo:contributor <", $personUri, '>';
        }
        if ($first->attribution) {
            echo " ;\n    sdo:copyrightNotice ", $this->ttlLiteral($first->attribution, null, 'rdf:HTML');
        }
        if ($first->date) {
            echo ' ;', "\n    sdo:dateCreated \"", substr($first->date, 0, 10), '"^^xsd:date';
        }
        echo "\n  ] ;\n";
    }

    private function printImage(Image $img): void
    {
        echo '<http://vici.org/image/', $img->id, '>', "\n";
        echo '  a sdo:ImageObject ;', "\n";
        echo '  rdfs:isDefinedBy <http://vici.org/image/', $img->id, '/rdf> ;', "\n";
        echo '  sdo:name ', $this->ttlLiteral($img->title), " ;\n";
        if ($img->description) {
            echo '  sdo:description ', $this->ttlLiteral($img->description), " ;\n";
        }
        echo '  sdo:contentUrl <', $this->sanitizeUri('https://images.vici.org/auto' . $img->filepath), '> ;', "\n";
        echo '  sdo:thumbnailUrl <', $this->sanitizeUri('https://images.vici.org/size/h200' . $img->filepath), '> ;', "\n";
        $licenseUri = isset($img->license) ? ($img->license->uri ?? null) : null;
        if ($licenseUri) {
            echo '  sdo:license <', $this->sanitizeUri($licenseUri), '> ;', "\n";
        }
        if ($img->dateAdded) {
            echo '  sdo:uploadDate "', substr($img->dateAdded, 0, 10), '"^^xsd:date ;', "\n";
        }
        if (!$img->isOwnWork && $img->source) {
            if (preg_match('#^https?://#i', $img->source)) {
                echo '  sdo:isBasedOn <', $this->sanitizeUri($img->source), '> ;', "\n";
            } else {
                echo '  sdo:isBasedOn ', $this->ttlLiteral($img->source), " ;\n";
            }
        }
        $uploader = isset($img->uploader) ? $img->uploader : null;
        if ($img->isOwnWork && $uploader && $uploader->getRealName() && $uploader->getId()) {
            $personUri = $this->personUri($uploader->getId(), $uploader->getRealName());
            echo '  sdo:creator <', $personUri, '>', "\n";
        } else {
            $last = '';
            if ($img->creator) {
                $last = '  sdo:copyrightNotice ' . $this->ttlLiteral('© ' . $img->creator);
            }
            if ($uploader && $uploader->getRealName() && $uploader->getId()) {
                if ($last) {
                    echo $last, " ;\n";
                }
                $personUri = $this->personUri($uploader->getId(), $uploader->getRealName());
                $last = '  sdo:contributor <' . $personUri . '>';
            }
            echo $last, "\n";
        }
        echo ".\n\n";
        echo '<http://vici.org/image/', $img->id, '/rdf>', "\n";
        echo '  a sdo:Dataset ;', "\n";
        echo '  rdfs:label "Meta resource for http://vici.org/image/', $img->id, '"@en ;', "\n";
        echo '  rdfs:label "Meta-resource voor http://vici.org/image/', $img->id, '"@nl ;', "\n";
        echo '  sdo:about <http://vici.org/image/', $img->id, '> ;', "\n";
        echo '  sdo:isPartOf <http://vici.org/dataset> ;', "\n";
        echo '  sdo:license <', $this->sanitizeUri($licenseUri ?: 'http://creativecommons.org/publicdomain/zero/1.0/'), '>', "\n";
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

    private function flushPersons(): void
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
        // normalise Unicode line/paragraph separators (U+2028/U+2029)
        $value = str_replace(["\xE2\x80\xA8", "\xE2\x80\xA9"], "\n", $value);
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

    /**
     * Port of the legacy ViciCommonLogic::parseAnnotation: expands
     * <cite class="reference"> markup into footnote links and a
     * reference list headed by a translated "References" title.
     */
    private function parseAnnotation(string $html, string $lang): string
    {
        if (empty($html)) {
            return $html;
        }

        $html = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
            '|(?<=^|[\x00-\x7F])[\x80-\xBF]+' .
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/',
            '�', $html);
        $html = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]' .
            '|\xED[\xA0-\xBF][\x80-\xBF]/S', '?', $html);

        $html = preg_replace("/&(?!\S+;)/", "&amp;", $html);

        libxml_use_internal_errors(true);
        $annotation = new \DOMDocument();
        $annotation->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html);

        $xpathsearch = new \DOMXPath($annotation);
        $nodes = $xpathsearch->query("//cite[contains(@class,'reference')]");

        $refs = '';
        $count = 1;
        foreach ($nodes as $node) {
            preg_match('/\[(.*)\]/', $node->nodeValue, $matches);
            $anchorText = isset($matches[1]) ? $matches[1] : '';
            if (is_object($node) && is_object($node->attributes) && is_object($node->attributes->getNamedItem('href')) && $node->attributes->getNamedItem('href')->nodeValue) {
                $refs .= '<li><a href="#cite_ref-' . $count . '">↑</a><a href="' . $node->attributes->getNamedItem('href')->nodeValue . '">' . $anchorText . '</a></li>';
            } else {
                $refs .= '<li><a href="#cite_ref-' . $count . '">↑</a>' . $anchorText . '</li>';
            }

            $refLink = $annotation->createElement('sup');
            $refLink->setAttribute('id', '#cite_note-' . $count);
            $refAnchor = $annotation->createElement('a', (string)$count);
            $refAnchor->setAttribute('href', '#cite_note-' . $count);
            $refLink->appendChild($refAnchor);
            $node->parentNode->replaceChild($refLink, $node);

            $count++;
        }
        $refsTitle = ($this->translators[$lang] ?? $this->translators['en'])->get('References');
        $refs = ($refs) ? '<h2>' . $refsTitle . '</h2><p><ol>' . $refs . '</ol></p>' : '';
        return str_replace(['<body>', '</body>'], '',
            $annotation->saveHTML($annotation->getElementsByTagName('body')->item(0))) . $refs;
    }

    private static function link_urls($text)
    {
        $rexProtocol = '(https?://)';
        $rexDomain   = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
        $rexPort     = '(:[0-9]{1,5})?';
        $rexPath     = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
        $rexQuery    = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff\|]+?)?';
        $rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff\|]+?)?';

        return preg_replace_callback("&$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$|((</)[^aA])))&",
            function ($match) {
                return '<a href="' . $match[0] . '">' . $match[1] . $match[2] . $match[3] . $match[4] . $match[5] . '</a>';
            }, $text);
    }
}
