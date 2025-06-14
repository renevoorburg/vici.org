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
Shows recents additions - images, places - and a list of users that added most items.
 */

require_once dirname(__FILE__).'/include/classLang.php';
require_once dirname(__FILE__).'/include/classSession.php';
require_once dirname(__FILE__).'/include/classViciCommon.php';
require_once dirname(__FILE__).'/include/classDBConnector.php';
require_once dirname(__FILE__).'/include/classSiteKinds.php';
require_once dirname(__FILE__).'/include/classPage.php';

$lng = new Lang();
$session = new Session($lng->getLang());
$db = new DBConnector(); // no errorhandling ...
$siteKinds = new SiteKinds($lng);


function getItemHTML(Lang $lngObj, $itemId, $kindName, $pntId, $itemName, $user, $date, $image)
{
    $lf = "\n";

    $ret = '';
    $ret .= '<div style="position:relative; display:inline-block;vertical-align:text-top;width:220px;height:124px;margin:0 4px 4px 0;background-color:rgb(164, 164, 164)">' .$lf;

    if ($image) {
        $ret .= '<img style="position:absolute;top:0;right:0;left:0" src="//images.vici.org/crop/w220xh124' . $image . '">' . $lf;
        $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId. '/'    ) . '"><img title="' . $kindName . '" style="position:absolute;top:4px;left:4px" class="icon' . $itemId . ' marker" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="></a>' . $lf;
        $ret .= '<div style="position:absolute;top:0;left:40px;right:0;bottom:0;margin:0;padding:4px;background-image:url(/images/white_75.png)">'.$lf;
    } else {
        $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId . '/') . '"><img title="' . $kindName . '" style="position:absolute;top:4px;left:4px" class="icon' . $itemId . ' marker" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="></a>' . $lf;
        $ret .= '<div style="position:absolute;top:0;left:40px;right:0;bottom:0;margin:0;padding:4px;background-color:rgb(243, 243, 243)">';
    }

    $ret .= '<div style="margin-left:0">'.$lf;
    $ret .= '<h3><a class="black" href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId . '/') . '">' . $itemName . '</a></h3>'.$lf;
    $ret .= '<p style="color:#646464">' . date('j M Y H:i', strtotime($date)) . '<br>'.$user.'</p>' . $lf;


    $ret .= '</div>'.$lf;
    $ret .= '</div>'.$lf;
    $ret .= '</div>';

    return $ret;
}


//// create overview of top adders:
$query = "select count(*) as number, acc_name, acc_realname from points
left join pmetadata on pnt_id=pmeta_pnt_id
left join accounts on pmeta_creator=acc_id
where pnt_hide=0
group by pmeta_creator order by number desc
limit 10";

$topresult = $db->query($query);

$tophtml = '<p style="margin:0" class="grey">';
$i=0;
while ($row = $topresult->fetch_object()) {
    $i++;
    $numPoints = $row->number;
    $accName = $row->acc_name;
    $name = $row->acc_realname;
    //$tophtml.="<tr><td class='rightalign' style='color:#646464;padding:2px'>$i.</td><td style='padding:2px'>$name</td><td class='rightalign' style='padding:2px'><a class='black' href='/search.php?creator=$accName'>$numPoints</a></td></tr>\n";
    $tophtml.= $i.'.&nbsp;<span class="black">'.str_replace(' ', '&nbsp;', $name).'</span>&nbsp;('.$numPoints.') ';
}
$tophtml.="</p>";


//// create overview of top image adders:
$query = "select count(*) as number, acc_name, acc_realname from img_data
left join accounts on imgd_uploader=acc_id
group by acc_name order by number desc
limit 10";

$topresult = $db->query($query);

//$topimage = "<div style='float:right;clear:right;margin-right:0px;background-color:rgb(243, 243, 243);border:1px solid #D7D7D7'><h2 style='margin-bottom:2px; margin-top:2px;'>&nbsp;".$lng->str('Top uploaders').":</h2><table>\n";
$topimage = '<p style="color:#646464;margin:0">';
$i=0;
while ($row = $topresult->fetch_object()) {
    $i++;
    $numPoints = $row->number;
    $accName = $row->acc_name;
    $name = $row->acc_realname;
    $topimage.= $i.'.&nbsp;<span class="black">'.str_replace(' ', '&nbsp;', $name).'</span>&nbsp;('.$numPoints.') ';
}
$topimage.="</p>";


// create overview of recent uploaded images:

$result = $db->query("select img_id, img_path, imgd_title, acc_realname, imgd_date
                      from images
                      left join img_data on img_id=imgd_imgid
                      LEFT JOIN accounts ON imgd_uploader=acc_id
                      where img_hide=0
                      order by imgd_date desc limit 80");

$imagehtml = '';
while ($row = $result->fetch_object()) {

    $htmlpart = '<div style="display:inline-block;margin:0 4px 4px 0;vertical-align:text-top;"><a href="'. $lng->langURL($lng->getLang(), '/image.php?id='.$row->img_id).'" title="'.htmlspecialchars($row->imgd_title).'"><img style="float:left" src="//images.vici.org/size/h124'.$row->img_path.'"></a>';
    $htmlpart.= '<div style="display:inline-block;padding:0 4px 0 4px;max-width:180px;background-color: rgb(243, 243, 243);height:124px"><h3 style="padding-top:4px"><a class="black" href="'. $lng->langURL($lng->getLang(),'/image.php?id=' .$row->img_id).'">'.htmlspecialchars($row->imgd_title). '</a></h3><p style="color:#646464">' .date ('j M Y H:i', strtotime($row->imgd_date)).'<br>'.$row->acc_realname.'</p></div></div>';

    $htmlpartsArr[strtotime($row->imgd_date)] = $htmlpart;
//    $imagehtml.= $htmlpart;
}

//$imagehtml .= '</div>';


// create overview of recently added entries:
$query="SELECT pnt_id, pmeta_pnt_id, pnt_kind, pnt_name, pmeta_create_date, pnt_lat, pnt_lng, pmeta_creator, acc_realname, img_path
        FROM points LEFT JOIN pmetadata ON pnt_id = pmeta_pnt_id
        LEFT JOIN accounts ON pmeta_creator=acc_id
        LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1
        LEFT JOIN images ON pil_img=img_id
        WHERE pnt_hide=0
        ORDER BY pmeta_create_date DESC 
        LIMIT 80";
$result = $db->query($query);

$html = $imagehtml.'<h2 style="margin-bottom:6px">'.$lng->str('Recently added').'</h2>';
$html .= $tophtml;
while ($row = $result->fetch_object()) {
    $name = $row->pnt_name;
    $pnt_id =$row->pnt_id;

    $htmlpart = getItemHTML($lng, $row->pnt_kind, $lng->str($siteKinds->getName($row->pnt_kind)), $row->pnt_id, $row->pnt_name, $row->acc_realname, $row->pmeta_create_date, $row->img_path);
    $htmlpartsArr[strtotime($row->pmeta_create_date)] = $htmlpart;
//    $html.=$htmlpart;
}


// now sort all the entries bij date / timestamp and generate output:
ksort($htmlpartsArr);
$html = '';
$i = 28;
$tmpArr = array_reverse($htmlpartsArr);
foreach ($tmpArr as $htmlpart) {
    if ($i > 0) {
        $html .= $htmlpart;
    }
    $i--;
}

$html .= '<div style="background-color:rgb(243, 243, 243);padding:0 4px 0 4px">';
$html .= '<h3 class="grey">'.$lng->str('Top creators').':</h3>'.$tophtml;
$html .= '<div style="height:1px;border-bottom:1px solid #D7D7D7"></div>';
$html .= '<h3 class="grey">'.$lng->str('Top uploaders').':</h3>'.$topimage;
$html .= '</div>';


// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('content', $html);
$page->assign('sitesubtitle', $lng->str('Recently added'));
$page->assign('pagetitle', $lng->str('Recently added'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');
