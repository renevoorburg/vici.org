<?php

/**
Copyright 2013-6, René Voorburg, rene@digitopia.nl

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
Handles searches.
 */

require_once dirname(__FILE__).'/include/classLang.php';
require_once dirname(__FILE__).'/include/classSession.php';
require_once dirname(__FILE__).'/include/classViciCommon.php';
require_once dirname(__FILE__).'/include/classDBConnector.php';
require_once dirname(__FILE__).'/include/classSiteKinds.php';
require_once dirname(__FILE__).'/include/classPage.php';

$lng = new Lang();
$session = new Session($lng->getLang());
$siteKinds = new SiteKinds($lng);

$db = new DBConnector(); // no errorhandling ...

set_time_limit (30);
define("MAXITEMS", 50);


function getItemHTML(Lang $lngObj, $itemId, $kindName, $pntId, $itemName, $user, $text, $image)
{
    $lf = "\n";

    $ret = '';
    $ret .= '<div style="position:relative;display:inline-block;overflow:hidden;vertical-align:text-top;width:220px;height:124px;margin:0 4px 4px 0;background-color:rgb(164, 164, 164)">' .$lf;

    if ($image) {
        $ret .= '<img style="position:absolute;top:0;right:0;left:0" src="//images.vici.org/crop/w220xh124' . $image . '">' . $lf;
        $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId) . '"><img title="' . $kindName . '" style="position:absolute;top:4px;left:4px" class="icon' . $itemId . ' marker" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="></a>' . $lf;
        $ret .= '<div style="position:absolute;top:0;left:40px;right:0;bottom:0;margin:0;padding:4px;background-image:url(/images/white_75.png)">'.$lf;
    } else {
        $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId) . '"><img title="' . $kindName . '" style="position:absolute;top:4px;left:4px" class="icon' . $itemId . ' marker" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="></a>' . $lf;
        $ret .= '<div style="position:absolute;top:0;left:40px;right:0;bottom:0;margin:0;padding:4px;background-color:rgb(243, 243, 243)">';
    }

    $ret .= '<div style="margin-left:0">'.$lf;
    $ret .= '<h3><a class="black" href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId) . '">' . $itemName . '</a></h3>'.$lf;
    $ret .= '<p style="color:#646464">' . $text . '<br>' . $user . '</p>' . $lf;


    $ret .= '</div>'.$lf;
    $ret .= '</div>'.$lf;
    $ret .= '</div>';

    return $ret;
}


// manage input:
$input_errors = "";
$urlparams = "";

// format options 'html' (default) 'kml' or 'json'
$format = (isset($_GET['format'])) ? $db->real_escape_string($_GET['format']) : "html";
//if ($format=='html') {
    // paging - only html output is paged
    $page = (isset($_GET['page']) && (int)($_GET['page'])>0 ) ? (int)($_GET['page']) : 1;
    $start = ($page-1)*MAXITEMS;
    $limit = "LIMIT $start,".MAXITEMS;
//} else {
//    $limit = "";
//    $urlparams.= "&format=".$format;
//}

if (isset($_GET['terms'])) {
//    $q = $db->real_escape_string($_GET['terms']);

    $terms = preg_replace('/\+/', ' ', $_GET['terms']);
    $q= $db->real_escape_string('+' . preg_replace('/\s+/', '+', trim($terms)));
    $order = " ORDER BY pnt_name='$q' DESC, match (pnt_name, pnt_dflt_short) against ('$q') DESC ";
    $urlparams.= "&terms=$q";
} else {
    $q = '';
    $order = '';
}

if (isset($_GET['category'])) {
    $category = $db->real_escape_string($_GET['category']);
    $category_filter = " AND pnt_kind='$category'";
    $urlparams.= "&category=".$category;
} else {
    $category_filter = '';
}

if (isset($_GET['extid'])) {
    $extid = $db->real_escape_string($_GET['extid']);
    if ( ($extid=='pleiades') || ($extid=='pleiades:') ) { 
        $extid_filter = " AND pmeta_extids LIKE '%<span>http://pleiades.stoa.org/places/%'";
    } else {
        $extids = explode(":", $extid);
        $extid_filter = " AND pmeta_extids LIKE '%<span>http://pleiades.stoa.org/places/".$extids[1]."/%'";
    }
    $urlparams.= "&extid=".$extid;
} else {
    $extid_filter="";
}

if (isset($_GET['creator'])) {
    $creator = $db->real_escape_string($_GET['creator']);
    if ((string)$creator==(string)(int)$creator) {
        $creator_filter = " AND pmeta_creator='$creator'";
    } else {
        $sql = "SELECT acc_id FROM accounts WHERE acc_name='$creator'";
        $result = $db->query($sql);
        $row = $result->fetch_object();
        $creator = $row->acc_id;
        $creator_filter=" AND pmeta_creator='$creator'";
    }
    $urlparams.= "&creator=".$creator;
} else {
    $creator_filter = '';
}

if (isset($_GET['bounds'])) {
    $bounds = explode(",", $_GET['bounds']);
    $bounds_filter = " AND pnt_lat > '".$bounds[0]."' AND pnt_lng > '".$bounds[1]."' AND pnt_lat < '".$bounds[2]."' AND pnt_lng < '".$bounds[3]."' ";
    $urlparams.= "&bounds=".$_GET['bounds'];
} else {
    $bounds_filter = '';
}

if (isset($_GET['from'])) {
    $from = $db->real_escape_string($_GET['from']);
    $from_filter = " AND pmeta_edit_date > '$from'";
    $order = " ORDER BY pmeta_edit_date ASC ";
    $urlparams.= "&from=".$from;
} else {
    $from_filter = '';
}


if (isset($_GET['near'])) {
    $coords = explode(",", $_GET['near']);
    $near_select = ", ( 6371 * acos( cos( radians(".$coords[0].") ) * cos( radians( pnt_lat ) ) * cos( radians( pnt_lng ) - radians(".$coords[1].") ) + sin( radians(".$coords[0].") ) * sin( radians( pnt_lat ) ) ) ) As D";
    $order = " ORDER BY D ASC ";
    if (isset($_GET['radius']) && (int)($_GET['radius'])>0  ) {
        $radius = $db->real_escape_string($_GET['radius']);
        $D = $radius/1000;
        $distance_filter = " AND ( 6371 * acos( cos( radians(".$coords[0].") ) * cos( radians( pnt_lat ) ) * cos( radians( pnt_lng ) - radians(".$coords[1].") ) + sin( radians(".$coords[0].") ) * sin( radians( pnt_lat ) ) ) )<$D";
         $urlparams.= "&near=".$_GET['near']."&radius=".$radius;
    } else {
        $urlparams.= "&near=".$_GET['near'];
    }
} else {
    $near_select = '';
    $distance_filter = '';
}

if ( ($format=='json') || ($format=='kml')) {
    $jsonselect = ', pnt_lat, pnt_lng, pnt_visible, pnt_promote, pkind_low, pkind_high, pkind_zindex';
} else {
    $jsonselect = '';
}
          
$sql =
"SELECT pnt_id, pnt_name,  psum_pnt_name, pnt_kind, pnt_dflt_short, img_path, psum_short$jsonselect$near_select
FROM points 
LEFT JOIN pmetadata ON pnt_id = pmeta_pnt_id
LEFT JOIN psummaries ON pnt_id = psum_pnt_id AND psum_lang='".$lng->getLang()."'
LEFT JOIN pkinds ON pkind_id=pnt_kind
LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1
LEFT JOIN images ON pil_img=img_id
WHERE pnt_id IN (
    SELECT DISTINCT pnt_id FROM points 
          LEFT JOIN ptexts ON pnt_id = ptxt_pnt_id 
          LEFT JOIN psummaries ON pnt_id = psum_pnt_id 
		  LEFT JOIN pmetadata ON pnt_id = pmeta_pnt_id
          LEFT JOIN (
          	SELECT acc_id as editor_id, acc_name as editor_name, acc_realname as editor_realname
          	FROM accounts
          ) AS u ON ptxt_editor = editor_id   
          LEFT JOIN (
          	SELECT acc_id as creator_id, acc_name as creator_name, acc_realname as creator_realname
          	FROM accounts
          ) AS v ON pmeta_creator = creator_id 
          LEFT JOIN (
          	SELECT acc_id as updater_id, acc_name as updater_name, acc_realname as updater_realname
          	FROM accounts
          ) AS w ON pmeta_editor = updater_id 
          LEFT JOIN pnt_img_lnk ON pnt_id = pil_pnt
          LEFT JOIN img_data ON pil_img = imgd_imgid
          WHERE pnt_hide=0 AND (
            match (pnt_name, pnt_dflt_short) against ('$q' IN BOOLEAN MODE)  OR 
            match(psum_short, psum_pnt_name)  against ('$q' IN BOOLEAN MODE) OR 
            match(ptxt_full) against ('$q' IN BOOLEAN MODE) OR
            match(imgd_title, imgd_description) against ('$q' IN BOOLEAN MODE)
           ) $category_filter $creator_filter $bounds_filter $from_filter $extid_filter
          
)  $distance_filter $order $limit";

$result = $db->query($sql);


if ($format=='html') {

    $countquery = 
    "SELECT count(*) 
    FROM points 
    LEFT JOIN pmetadata ON pnt_id = pmeta_pnt_id
    LEFT JOIN psummaries ON pnt_id = psum_pnt_id AND  psum_lang='".$lng->getLang()."'
    LEFT JOIN pkinds ON pkind_id=pnt_kind
    WHERE pnt_id IN (
    SELECT DISTINCT pnt_id FROM points 
          LEFT JOIN ptexts ON pnt_id = ptxt_pnt_id 
          LEFT JOIN psummaries ON pnt_id = psum_pnt_id 
		  LEFT JOIN pmetadata ON pnt_id = pmeta_pnt_id
          LEFT JOIN (
          	SELECT acc_id as editor_id, acc_name as editor_name, acc_realname as editor_realname
          	FROM accounts
          ) AS u ON ptxt_editor = editor_id   
          LEFT JOIN (
          	SELECT acc_id as creator_id, acc_name as creator_name, acc_realname as creator_realname
          	FROM accounts
          ) AS v ON pmeta_creator = creator_id 
          LEFT JOIN (
          	SELECT acc_id as updater_id, acc_name as updater_name, acc_realname as updater_realname
          	FROM accounts
          ) AS w ON pmeta_editor = updater_id 
        LEFT JOIN pnt_img_lnk ON pnt_id = pil_pnt
          LEFT JOIN img_data ON pil_img = imgd_imgid
          WHERE pnt_hide=0 AND (
            match (pnt_name, pnt_dflt_short) against ('$q' IN BOOLEAN MODE)  OR 
            match(psum_short, psum_pnt_name)  against ('$q' IN BOOLEAN MODE) OR 
            match(ptxt_full) against ('$q' IN BOOLEAN MODE) OR
            match(imgd_title, imgd_description) against ('$q' IN BOOLEAN MODE)
            ) $category_filter $creator_filter $bounds_filter $from_filter $extid_filter
              
    )  $distance_filter ";

    $counter = $db->query($countquery);
    $countrow = $counter->fetch_row();
    $count = $countrow[0];
    $total_pages = ceil($count / MAXITEMS);

    if ($count > 0) {
        $html = '';
        while ($row = $result->fetch_object()) {
            $name = $row->pnt_name;
            $urlname = ViciCommon::urlencodeVici(preg_replace('/ /', '_', $name));
            $name = ($row->psum_pnt_name=='') ? $name :  $row->psum_pnt_name;
            $text = ($row->psum_short=='') ? $row->pnt_dflt_short : $row->psum_short ;
           // $html.= "<li style=\"background-image: url(/images/".ViciCommon::$pkinds[$row->pnt_kind][0]."_minimal.png);\"><a href='/vici/".$row->pnt_id."'>$name</a> $text</li>\n";

            $html .= getItemHTML($lng, $row->pnt_kind, $lng->str($siteKinds->getName($row->pnt_kind)), $row->pnt_id, $name, '' , $text, $row->img_path);


        }
        //$html.='';
        
        if ($total_pages > 1) {
            $html.= '<p>'.$lng->str('Page').': ';
            for ($i=1; $i<=$total_pages; $i++) { 
                if ($i==$page) {
                    $html.= "<strong>".$i."</strong> ";
                } else {
                    $html.= "<a href='search.php?page=".$i.$urlparams."'>".$i."</a> "; 
                }
            };
            $html.= '</p>';
        };
        // kml download link
        $urlparams = str_replace("format=html", "", $urlparams);
        $urlparams = substr($urlparams, 1);
        $html.= "<p><a href='/search.php?".$urlparams."&format=kml' style='color:black'><img src='/images/kml.png' style='vertical-align:sub'> ".$lng->str('Download as KML').'</a></p>';
    } else {
        $html = "<p>".$lng->str('Nothing found')."</p>";
    }

    // display page:
    $page = new Page();

    $page->assign('lang', $lng->getLang());
    $page->assign('terms', $_GET['terms']);
    $page->assign('content', $html);
    $page->assign('pagetitle', $lng->str('Search results'));
    $page->assign('session', ViciCommon::sessionBox($lng, $session));
    $page->assign('leftnav', ViciCommon::mainMenu($lng));

    $page->display('content.tpl');

} else if ($format=='kml') {
    if (!headers_sent()) { 
        header("Content-Disposition: attachment; filename=vici.kml");
        header('Content-Type:application/vnd.google-earth.kml+xml; charset=UTF-8');     
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<kml xmlns="http://www.opengis.net/kml/2.2">'."\n";
    echo "<Document>\n";
   
    while (list($pnt_id, $pnt_name, $psum_pnt_name, $pnt_kind,  $pnt_dflt_short, $img_path, $psum_short, $pnt_lat, $pnt_lng, $pnt_visible, $pnt_promote, $pkind_low, $pkind_high, $pkind_zindex ) = $result->fetch_row()) {
        
        $pnt_url = preg_replace('/ /', '_', $pnt_name);
        if (!empty($psum_pnt_name)) {$pnt_name=$psum_pnt_name;}
        if (!empty($psum_short)) {$pnt_dflt_short=$psum_short;}
            
        echo "<Placemark>\n";
        echo '<name>'.htmlspecialchars($pnt_name)."</name>\n";
        echo '<description>'.htmlspecialchars($pnt_dflt_short)."</description>\n";
        echo "<Point>\n";
        echo '<coordinates>'.$pnt_lng.','.$pnt_lat.",0</coordinates>\n";
        echo "</Point>\n";
        echo "</Placemark>\n";
    };
    echo "</Document>\n";
    echo '</kml>';
    
} else if ($format=='json') {
    if (!headers_sent()) { header('Content-Type:application/json; charset=UTF-8'); } ;
    echo "{ \"type\": \"FeatureCollection\",\n";
    echo "  \"features\": [\n";
    $sepx="";

    while (list($pnt_id, $pnt_name, $psum_pnt_name, $pnt_kind,  $pnt_dflt_short, $img_path, $psum_short, $pnt_lat, $pnt_lng, $pnt_visible, $pnt_promote, $pkind_low, $pkind_high, $pkind_zindex ) = $result->fetch_row()) {

        $pnt_url = preg_replace('/ /', '_', $pnt_name);
        if (!empty($psum_pnt_name)) {$pnt_name=$psum_pnt_name;}
        if (!empty($psum_short)) {$pnt_dflt_short=$psum_short;}

        if ($pnt_promote) { 
            $pkind_high = $pkind_high-1;
            $pkind_zindex = $pkind_zindex+2;
        }

        echo $sepx."  {\"type\": \"Feature\",\n";
        echo "  \"geometry\": {\"type\": \"Point\", \"coordinates\": [".$pnt_lng.", ".$pnt_lat."]},\n";
        echo "  \"properties\": {\"id\": ".$pnt_id.", \"url\": \"".ViciCommon::$url_base.ViciCommon::urlencodeVici($pnt_url).$lng->getLangGET('?')."\", \"title\": ".json_encode($pnt_name).", \"kind\": \"".ViciCommon::$pkinds[$pnt_kind][0]."\", \"zoomsmall\": ".$pkind_low.", \"zoomnormal\": ".$pkind_high.", \"zindex\": ".$pkind_zindex.", \"isvisible\": ".$pnt_visible.", \"summary\": ".json_encode($pnt_dflt_short)."}\n";
        echo "  }";
        $sepx=",\n";
    };
    echo "\n]}";
}
