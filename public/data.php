<?php

/**
Copyright 2013-2018, René Voorburg, rene@digitopia.nl

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
Spits out json points and lines.
*/

/**
 *  2018-05-25
 *  added a 'required' call param that ensures supplied ids are presented
 */

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');

ViciCommon::handlePreflightReq();

$lng = new Lang();
$db = new DBConnector(); // no errorhandling ... 

$isJsonpReq = isset($_GET['callback']);
$isNumericReq = isset($_GET['numeric']); // output categories for points should be ints, not names

ViciCommon::sendCORSHeaders($isJsonpReq);

//  A 'perspective' shows only markers that have a specified external identifier.
//  Links to the related external site are added to the output. 
//  Perspectives are only applied when a boundary / zoom based subset is requested.
$perspective = (isset($_GET['perspective']) ? $_GET['perspective'] : '');

// Set general SQL modifiers for when we are using a perspective on the data:
$perspectiveLineJoinSQL = ($perspective ? " LEFT JOIN pmetadata ON line_pnt_id=pmeta_pnt_id " : "");
$perspectivePointJoinSQL = " LEFT JOIN pmetadata ON pnt_id=pmeta_pnt_id ";

// set specific (SQL) modifiers for when using a perspective: 
$perspectiveArr = array ( 
    ''         => array ( "",                         "",                          ""),
    'pleiades' => array ( " AND pmeta_pleiades > 0 ", ", pmeta_pleiades AS extid ", "http://pleiades.stoa.org/places/$1"),
    'livius'   => array ( " AND pmeta_livius > '' ",  ", pmeta_livius AS extid ",   "http://livius.org/$1"),
    'romaq'    => array ( " AND pmeta_romaq > 0 ",    ", pmeta_romaq AS extid ",    "http://romaq.org/the-project/aqueducts/article/$1")
);
$perspectiveRestrictSQL = $perspectiveArr[$perspective][0];
$perspectiveSelectSQL   = $perspectiveArr[$perspective][1];
$perspectiveLinkTempl   = $perspectiveArr[$perspective][2];

// 'include' points are added non-the-less
$requiredPointsArr = array();
if (isset($_GET['require'])) {
    foreach (explode(",", $_GET['require']) as $value) {
        $requiredPointsArr[intval($value)] = true;
    }
}

// when called with focus param only restrict query to show just one point & its lines:
$focusLineRestrictSQL = '';
$focusPointRestrictSQL = '';
if (isset($_GET['focus'])) {
    $focusPointRestrictSQL = 'AND pnt_id='.(int)$_GET['focus'];
    $focusLineRestrictSQL =  'AND line_pnt_id='.(int)$_GET['focus'];
}

$showZoomedArea = !isset($_GET['focus']) && isset($_GET['zoom']) && isset($_GET['bounds']);

// First we deal with the lines:
if ($showZoomedArea) {
	$zoom = (int)$_GET['zoom'];
	$bounds = explode(",", $_GET['bounds']);
	$minLat = (float)$bounds[0];
	$minLng = (float)$bounds[1];
	$maxLat = (float)$bounds[2];
	$maxLng = (float)$bounds[3];  
	 
    //  line query for the given view:       
    $sql  = "SELECT line_id, line_pnt_id, line_kind, line_note, pldata_tozoom, pldata_points "
            . "FROM plines " 
            . "LEFT JOIN pline_data ON line_id=pldata_pline_id "
            . $perspectiveLineJoinSQL
            . "WHERE line_hide=0 AND $zoom >= pldata_fromzoom AND $zoom < pldata_tozoom "
            . $perspectiveRestrictSQL
            . "AND (($minLat < line_ne_lat) AND ($maxLat > line_sw_lat) AND ($minLng < line_ne_lng) AND ($maxLng > line_sw_lng)) ";            
} else {
    // line query everything:
    $sql  = "SELECT line_id, line_pnt_id, line_kind, line_note, pldata_tozoom, pldata_points "
            . "FROM plines "
            . "LEFT JOIN pline_data ON line_id=pldata_pline_id "
            . "WHERE line_hide=0 AND pldata_tozoom=99 "
            . $focusLineRestrictSQL;
}

$result = $db->query($sql); // no error handling

if ($isJsonpReq) echo $_GET['callback'],'(';

//$requiredPointsArr = array();

$sepx = "";
echo "{ \"lines\": [";
while (list($line_id, $line_pnt_id, $line_kind, $line_note, $pldata_tozoom, $pldata_points) = $result->fetch_row()) {
    $requiredPointsArr[$line_pnt_id] = true;
    echo $sepx."{\"kind\":";
    switch ($line_kind) {
    case 1:
        echo "\"road\",";
        break;
    case 2:
        echo "\"aqueduct\",";
        break;
    case 3:
        echo "\"canal\",";
        break;
    case 4:
        echo "\"wall\",";
        break;
    case 5:
        echo "\"other\",";
        break;  
    }
    echo "\"id\": $line_id,";
    echo "\"marker\":$line_pnt_id,";
    echo "\"expire\":$pldata_tozoom,";
    echo "\"note\":".json_encode($line_note).",";
    echo "\"points\":$pldata_points";
    echo "}";
    $sepx = ",";
}
echo "], ";


// points:

$isFlat = isset($_GET['flat']);
$zoomRestrictSQL = $isFlat ? "" : "AND pkind_low <= $zoom ";

if ($showZoomedArea) { 
    
    // create SQL to assure all points related to selected lines (code above) are included:
    $additionalPointsSQL = "";
//    foreach($requiredPointsArr as $line_pnt_id => $ignored) {
//        $additionalPointsSQL .= " OR pnt_id=$line_pnt_id";
//    }

    if (!empty($requiredPointsArr)) {
        $additionalPointsSQL = " OR pnt_id IN (". implode(",", array_keys($requiredPointsArr)) . ")";
    }

    $sql  = "SELECT pnt_id, pnt_name, pnt_kind, pnt_lat, pnt_lng, pnt_visible, pnt_dflt_short, pnt_promote, pkind_low, pkind_high, pkind_zindex, psum_pnt_name, psum_short, img_path, pmeta_loc_accuracy "
            .$perspectiveSelectSQL
            . "FROM points " 
            . "LEFT JOIN pkinds ON pkind_id=pnt_kind "
            . "LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1 "
            . "LEFT JOIN images ON pil_img=img_id "
            //. "LEFT JOIN pmetadata ON pnt_id=pmeta_pnt_id "
            . "LEFT JOIN psummaries ON pnt_id=psum_pnt_id AND psum_lang='".$lng->getLang()."' "
            .$perspectivePointJoinSQL
            . "WHERE pnt_hide=0 "
          	. "AND pnt_lat > $minLat AND pnt_lat < $maxLat AND pnt_lng > $minLng AND pnt_lng < $maxLng "
          	.$zoomRestrictSQL
          	.$perspectiveRestrictSQL
          	.$additionalPointsSQL ;
} else {
	$sql  = "SELECT pnt_id, pnt_name, pnt_kind, pnt_lat, pnt_lng, pnt_visible, pnt_dflt_short, pnt_promote, pkind_low, pkind_high, pkind_zindex, psum_pnt_name, psum_short, img_path, pmeta_loc_accuracy "
	        . "FROM points " 
            . "LEFT JOIN pkinds ON pkind_id=pnt_kind "
            . "LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1 "
            . "LEFT JOIN images ON pil_img=img_id "
            . "LEFT JOIN pmetadata ON pnt_id=pmeta_pnt_id "
            . "LEFT JOIN psummaries ON pnt_id=psum_pnt_id AND psum_lang='".$lng->getLang()."' "
            . "WHERE pnt_hide=0 "
            . $focusPointRestrictSQL;
}

//echo $sql;
//exit;

$result = $db->query($sql); // no error handling

$sepx = "";
echo "\"type\":\"FeatureCollection\",\"features\":[";

while($obj = $result->fetch_object()) {
    
    // prepare output:
    $name  = (empty($obj->psum_pnt_name) ? $obj->pnt_name : $obj->psum_pnt_name);
    $short = (empty($obj->psum_short) ? $obj->pnt_dflt_short : $obj->psum_short);
    $kind = ($isNumericReq ? $obj->pnt_kind : ViciCommon::$pkinds[$obj->pnt_kind][0]);
//    $fullUrl = $perspective
//        ? str_replace('$1', str_replace('=', '/', $obj->extid), $perspectiveLinkTempl)
//        : ViciCommon::$url_base.$obj->pnt_id."/".ViciCommon::urlencodeVici(str_replace(' ', '_', $obj->pnt_name)).$lng->getLangGET('?');

    $fullUrl = $perspective
        ? str_replace('$1', str_replace('=', '/', $obj->extid), $perspectiveLinkTempl)
        : ViciCommon::$url_base.$obj->pnt_id.'/'.$lng->getLangGET('?');


    // write output:
    echo $sepx."{\"type\":\"Feature\",";
    echo "\"geometry\":{\"type\":\"Point\",\"coordinates\":[",$obj->pnt_lng,",",$obj->pnt_lat,"]},";
    echo "\"properties\":{\"id\":",$obj->pnt_id,",";
    echo "\"url\":\"",$fullUrl,"\",";
    echo "\"title\":",json_encode($name),",";
    echo "\"kind\":\"",$kind,"\",";
    if ($isFlat) {
        echo "\"zoomsmall\":1,";
    } else {
        echo "\"zoomsmall\":",$obj->pkind_low,",";
    }
    echo "\"zoomnormal\":",$obj->pkind_high,",";
    echo "\"zindex\":",$obj->pkind_zindex,",";
    echo "\"isvisible\":",$obj->pnt_visible,",";
    echo "\"identified\":",($obj->pmeta_loc_accuracy=='5'?"false":"true"),",";
    echo "\"summary\":",json_encode($short),",";
    echo "\"img\":",json_encode($obj->img_path);
    echo "}}";
    $sepx=",";
}
    
echo "]}";

if ($isJsonpReq) echo ');';
