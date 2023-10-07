<?php

/**
Copyright 2014, René Voorburg, rene@digitopia.nl

This file is part of the Vici.org source.

Vici.org source is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Vici.org  source is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Vici.org source.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once (dirname(__FILE__).'/classSite.php');
require_once (dirname(__FILE__).'/classLineData.php');
require_once (dirname(__FILE__).'/classXMLNode.php');


class KML extends XMLNode
{
   
    public function __construct($idStr) {
        $idArr = explode("/", $idStr);
        $site = new Site('en', $idArr[0]);
        $id = $site->getId();
        $lat = $site->getLat();
        $lng = $site->getLng();
        $coordStr = "$lng,$lat,0 ";

        $placemark = new XMLNode('Placemark', new XMLNode('name', $site->getName()));
        $placemark->addNode(new XMLNode('description', htmlspecialchars($site->getSummary())));
        $placemark->addNode(new XMLNode('Point', new XMLNode('coordinates', $coordStr)));
        
        $folder = new XMLNode('Folder');
        $folder->addNode(new XMLNode('name', $site->getName()));
        $folder->addNode(new XMLNode('description', "Data downloaded from http://vici.org/vici/$id")); 
        $folder->addNode($placemark);

        $kmlline = new LineData($id);
        $kmlArr = $kmlline->getKML();
        foreach ($kmlArr as $i=>$line) {
            $placemark = new XMLNode('Placemark', new XMLNode('name', $site->getName().", line part ".($i+1)));

            if ($kmlline->isFree()) {
                $descStr = $kmlline->getLicense().' by '.$kmlline->getAuthor();
                $descStr .= $kmlline->getAttribution() ? ' - '.$kmlline->getAttribution() : '';
            } else {
                $descStr = 'Line data is not available under a free license. Included is a simplified representation.';
            }
            $placemark->addNode(new XMLNode('description', htmlspecialchars($descStr)));
            $linestring = new XMLNode('LineString', "<tessellate>1</tessellate><coordinates>$line</coordinates>");
            $placemark->addNode($linestring);
            $folder->addNode($placemark);
        }

        parent::__construct('kml', $folder, 'xmlns="http://www.opengis.net/kml/2.2"');
    }
    
    
    public function __destruct()
    {
        if (!headers_sent()) { 
            header("Content-Disposition: attachment; filename=vici.kml");
            header('Content-Type:application/vnd.google-earth.kml+xml; charset=UTF-8');     
        }               
        echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        parent::printNode(true);
    }
    
    
    
}