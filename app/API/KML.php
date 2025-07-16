<?php

namespace Vici\API;

use PDO;
use Vici\Session\Session;
use Vici\DB\DBConnector;
use Vici\Model\Site\SiteRepository;
use Vici\Geometries\Line;

class KML extends APICall
{
    /**
     * Zet de juiste headers voor KML-output (application/vnd.google-earth.kml+xml)
     */
    public function headers()
    {
        header('Content-Type: application/vnd.google-earth.kml+xml; charset=UTF-8');
        header('Cache-Control: public, max-age=300');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');
    }

    public Session $session;
    public DBConnector $db;
    
    public function __construct(Session $session)
    {
        parent::__construct($session);
        $this->session = $session;
        $this->db = $session->getDBConnector();
    }
    
    public function payload()
    {

        $siteRepo = new SiteRepository($this->db);
        $site = $siteRepo->getById($this->session->getRequestedItem());


        $id = $site->id;
        $lat = $site->representativeLocation->latitude;
        $lng = $site->representativeLocation->longitude;
        $name = $site->locales[$this->session->getLanguage()]->title;
        $summary = $site->locales[$this->session->getLanguage()]->description;
        $url = "https://vici.org/vici/$id";
        $coordStr = "$lng,$lat,0 ";

        // Bouw KML op met DOMDocument
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // <kml>
        $kml = $dom->createElement('kml');
        $kml->setAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
        $dom->appendChild($kml);

        // <Folder>
        $folder = $dom->createElement('Folder');
        $kml->appendChild($folder);

        $folder->appendChild($dom->createElement('name', $name));
        $folder->appendChild($dom->createElement('description', "Data downloaded from $url"));

        // <Placemark> voor het punt
        $placemark = $dom->createElement('Placemark');
        $placemark->appendChild($dom->createElement('name', $name));
        $placemark->appendChild($dom->createElement('description', htmlspecialchars("$url - " . $summary)));
        $point = $dom->createElement('Point');
        $point->appendChild($dom->createElement('coordinates', $coordStr));
        $placemark->appendChild($point);
        $folder->appendChild($placemark);

        // <Placemark> voor elke lijn
        $kmlline = new LineData($id);
        $kmlArr = $kmlline->getKML();
        foreach ($kmlArr as $i => $line) {
            $placemark = $dom->createElement('Placemark');
            $placemark->appendChild($dom->createElement('name', $name . ', line part ' . ($i + 1)));

            if ($kmlline->isFree()) {
                $descStr = "Line data from $url - ";
                $descStr .= $kmlline->getLicense() . ', by ' . $kmlline->getAuthor();
                $descStr .= $kmlline->getAttribution() ? ' - ' . $kmlline->getAttribution() : '';
            } else {
                $descStr = 'Line data is not available under a free license. Included is a simplified representation.';
            }
            $placemark->appendChild($dom->createElement('description', htmlspecialchars($descStr)));

            $linestring = $dom->createElement('LineString');
            $linestring->appendChild($dom->createElement('tessellate', '1'));
            $linestring->appendChild($dom->createElement('coordinates', $line));
            $placemark->appendChild($linestring);

            $folder->appendChild($placemark);
        }

        // Output als string (je kunt dit als response teruggeven)
        $this->kmlString = $dom->saveXML();







    }

}
