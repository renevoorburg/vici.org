<?php

/**
Copyright 2013-8, René Voorburg, rene@digitopia.nl

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

/**
Spits out json of highlights for given area.
 *
 *
 *  2018-05-23 : support new names for era (openlayers widget)
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');

ViciCommon::handlePreflightReq();

$lng = new Lang();
$db = new DBConnector(); // no errorhandling ... 

$isJsonpReq = isset($_GET['callback']);
$isNumericReq = isset($_GET['numeric']); // output categories for points should be ids (integers), not names

ViciCommon::sendCORSHeaders($isJsonpReq);

$zoom = (int)$_GET['zoom'];
$bounds = explode(",", $_GET['bounds']);
$minLat = (float)$bounds[0];
$minLng = (float)$bounds[1];
$maxLat = (float)$bounds[2];
$maxLng = (float)$bounds[3];

// either we focus on the center of the map or we read out the 'focus' parameter:
if (isset($_GET['focus'])) {
    $coords = explode(",", $_GET['focus']);
    $centerLat = $coords[0];
    $centerLng = $coords[1];
    $orderby = "distance";
} else {
    $centerLat = $minLat + ($maxLat - $minLat) / 2;
    $centerLng = $minLng + ($maxLng - $minLng) / 2;
    $orderby = "distance * RAND()";
}

$exclude = isset($_GET['exclude']) ? (int)$_GET['exclude'] : 0;
$n = (isset($_GET['n']) ? (int)$_GET['n'] : 2);

$extraSQL = '';
if (isset($_GET['era'])) {
    $era = $_GET['era']; 
    if (($era == 'onlyContemporary') || ($era == 'contemporaryEra')) {
        $extraSQL = ' AND (pnt_kind=8 OR pnt_kind=19)';    
    } elseif (($era == 'onlyRoman')| ($era == 'historicalEra')) {
        $extraSQL = ' AND  NOT (pnt_kind=8 OR pnt_kind=19)'; 
    }
}
if (isset($_GET['visibility']) && ($_GET['visibility'] == 'onlyVisible')) {
    $extraSQL .= ' AND (pnt_visible=1)';    
}

//  A 'perspective' shows only markers that have a specified external identifier.
//  Links to the related external site are added to the output. 
//  Perspectives are only applied when a boundary / zoom based subset is requested.
$perspective = (isset($_GET['perspective']) ? $_GET['perspective'] : '');
$perspectivePointJoinSQL = ($perspective ? " LEFT JOIN pmetadata ON pnt_id=pmeta_pnt_id " : "");

// TODO some stuff similar to geojson.php, should move to NormalizersIndex

// set specific (SQL) modifiers for when using a perspective: 
$perspectiveArr = array ( 
    ''         => array ( "",                         "",                          ""),
    'pleiades' => array ( " AND pmeta_pleiades > 0 ", ", pmeta_pleiades AS extid ", "http://pleiades.stoa.org/places/$1"),
    'livius'   => array ( " AND pmeta_livius > '' ",  ", pmeta_livius AS extid ",   "http://livius.org/place/$1"),
    'mithraeum'=> array ( " AND pmeta_mithraeum > '' ",  ", pmeta_mithraeum AS extid ",   "http://livius.org/place/$1"),
    'romaq'    => array ( " AND pmeta_romaq > 0 ",    ", pmeta_romaq AS extid ",    "http://romaq.org/the-project/aqueducts/article/$1")
);
$perspectiveRestrictSQL = $perspectiveArr[$perspective][0];
$perspectiveSelectSQL   = $perspectiveArr[$perspective][1];
$perspectiveLinkTempl   = $perspectiveArr[$perspective][2];

// . "(POW($centerLat - pnt_lat, 2) + POW(($centerLng - pnt_lng)*1.6, 2))/(pkind_zindex + 40*pnt_promote) AS distance "

$sql  = "SELECT pnt_id, pnt_name, pnt_lng, pnt_lat, pnt_kind, pnt_dflt_short, pkind_zindex, psum_pnt_name, psum_short, img_path, "
        . "6371*acos(cos(radians($centerLat))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians($centerLng))+sin(radians($centerLat))*sin(radians(pnt_lat))) AS distance "
        . $perspectiveSelectSQL
        . "FROM points "
        . "LEFT JOIN psummaries ON pnt_id = psum_pnt_id AND psum_lang='".$lng->getLang()."'" 
        . "LEFT JOIN pkinds ON pkinds.pkind_id=points.pnt_kind "
        . "LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1 "
        . "LEFT JOIN images ON pil_img=img_id AND img_hide=0 "
        . $perspectivePointJoinSQL
        . "WHERE (pnt_lat > $minLat) AND (pnt_lat < $maxLat) AND (pnt_lng > $minLng) AND (pnt_lng < $maxLng) "
        .      "AND (pnt_hide=0) AND (pkind_low <= $zoom) "
        .      "AND ((pnt_promote=1) OR (pil_dflt=1)) "
        .      "AND pnt_id != $exclude "
        .      $extraSQL
        .      $perspectiveRestrictSQL
        .  "ORDER BY $orderby ASC "
        .  "LIMIT $n ";


//echo $sql;

$result = $db->query($sql);

if ($isJsonpReq) echo $_GET['callback'],'(';

$sepx = '';
echo '{ "type": "FeatureCollection",'."\n"; 
echo '"features": ['."\n"; 

while ($obj = $result->fetch_object()) {
    
    // prepare output:
    $name  = (empty($obj->psum_pnt_name) ? $obj->pnt_name : $obj->psum_pnt_name);
    $short = (empty($obj->psum_short) ? $obj->pnt_dflt_short : $obj->psum_short);
    $kind = ($isNumericReq ? $obj->pnt_kind : ViciCommon::$pkinds[$obj->pnt_kind][0]);
    $fullUrl = $perspective 
        ? str_replace('$1', str_replace('=', '/', $obj->extid), $perspectiveLinkTempl) 
        : ViciCommon::$url_base.$obj->pnt_id."/".ViciCommon::urlencodeVici(str_replace(' ', '_', $obj->pnt_name)).$lng->getLangGET('?');

    if ($isNumericReq) {
        $kindStr = $obj->pnt_kind;
    } else {
        $kindStr = '"' . ViciCommon::$pkinds[$obj->pnt_kind][0] . '"';
    };

    // output
    echo $sepx."  {\"type\": \"Feature\",\n"; 
    echo "   \"geometry\": {\"type\": \"Point\", \"coordinates\": [".$obj->pnt_lng.", ".$obj->pnt_lat."]},\n";
    echo "   \"properties\": {\"id\": ".$obj->pnt_id.", \"url\": \"".$fullUrl."\", \"title\": ".json_encode($name).", \"kind\": $kindStr, \"summary\": ".json_encode($short).", \"img\": ".json_encode($obj->img_path)."}\n";
	echo "  }";
	$sepx=",\n";
};
echo "\n]}";	

if ($isJsonpReq) echo ');';
