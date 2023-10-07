<?php

/**
Shows recent changed places and a list of users that changed / editted most.
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


function getItemHTML(Lang $lngObj, $itemId, $kindName, $pntId, $itemName, $user, $date, $image) : string
{
    $lf = "\n";

    $ret = '<div style="position:relative; display:inline-block;vertical-align:text-top;width:220px;height:124px;margin:0 4px 4px 0;background-color:rgb(164, 164, 164)">' .$lf;

    if ($image) {
        $ret .= '<img style="position:absolute;top:0;right:0;left:0" src="//static.vici.org/cache/220x124-2' . $image . '">' . $lf;
        $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId.'/') . '"><img title="' . $kindName . '" style="position:absolute;top:4px;left:4px" class="icon' . $itemId . ' marker" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="></a>' . $lf;
        $ret .= '<div style="position:absolute;top:0;left:40px;right:0;bottom:0;margin:0;padding:4px;background-image:url(/images/white_75.png)">'.$lf;
    } else {
        $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId.'/') . '"><img title="' . $kindName . '" style="position:absolute;top:4px;left:4px" class="icon' . $itemId . ' marker" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="></a>' . $lf;
        $ret .= '<div style="position:absolute;top:0;left:40px;right:0;bottom:0;margin:0;padding:4px;background-color:rgb(243, 243, 243)">';
    }

    $ret .= '<div style="margin-left:0">'.$lf;
    $ret .= '<h3><a class="black" href="' . $lngObj->langURL($lngObj->getLang(), '/vici/' . $pntId.'/') . '">' . $itemName . '</a></h3>'.$lf;
    $ret .= '<p style="color:#646464">' . date('j M Y H:i', strtotime($date)) . '<br>' . $user . '</p>' . $lf;


    $ret .= '</div>'.$lf;
    $ret .= '</div>'.$lf;
    $ret .= '</div>';

    return $ret;
}


/** Obtain list of top 'changers':  */
//$arcDb = new DBConnector('archive'); // no errorhandling
//
//$sql = "SELECT COUNT( * ) AS number, acc_name, acc_realname FROM pnts_versions LEFT JOIN accounts ON pver_reccreator = acc_id GROUP BY pver_reccreator ORDER BY number DESC LIMIT 10";
//$topresult = $arcDb->query($sql);
//
//$tophtml = '<p style="margin:0" class="grey">';
//$i=0;
//while ($row = $topresult->fetch_object()) {
//    $i++;
//    $numPoints = $row->number;
//    $accName = $row->acc_name;
//    $name = $row->acc_realname;
//    $tophtml.= $i.'.&nbsp;<span class="black">'.str_replace(' ', '&nbsp;', $name).'</span>&nbsp;('.$numPoints.') ';
//}
//$tophtml.= "</p>";

/** Obtain list of top 'changes':  */
$db = new DBConnector(); // no errorhandling 

$sql = "select distinct * from (
            (select pnt_id, meta.acc_realname as acc_realname, pmeta_edit_date as date, pnt_kind, pnt_lat, pnt_lng, pnt_name, img_path from points
            left join pmetadata on pmeta_pnt_id=pnt_id 
            left JOIN accounts as meta ON pmeta_editor = meta.acc_id
            LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1
            LEFT JOIN images ON pil_img=img_id
            where pnt_hide=0
            and NOT pmeta_create_date=pmeta_edit_date
            order by pmeta_edit_date desc
            limit 50)
            union
            (select pnt_id, text.acc_realname as acc_realname, ptxt_edit_date as date, pnt_kind, pnt_lat, pnt_lng, pnt_name, img_path from points
            left join ptexts on ptxt_pnt_id=pnt_id and ptxt_lang='".$lng->getLang()."'
            LEFT JOIN accounts as text ON ptxt_editor = text.acc_id
            LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1
            LEFT JOIN images ON pil_img=img_id
            where pnt_hide=0
            order by  ptxt_edit_date desc
            limit 50)
        ) as t
        order by date DESC
        limit 29 ";
$result = $db->query($sql);

//$html = $tophtml;
$html = '';
$lastId = 0; // prevent double entries, must be a better way...
while ($row = $result->fetch_object()) {
    $pnt_id = $row->pnt_id;
    if ($lastId!=$pnt_id) {
        $html .= getItemHTML($lng, $row->pnt_kind, $lng->str($siteKinds->getName($row->pnt_kind)), $row->pnt_id, $row->pnt_name, $row->acc_realname, $row->date, $row->img_path);
        $lastId = $pnt_id;
    }
}

//$html .= '<div style="background-color:rgb(243, 243, 243);padding:0 4px 0 4px">';
//$html .= '<h3 class="grey">'.$lng->str('Top editors').':</h3>';
//$html .= '</div>';

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('content', $html);
$page->assign('sitesubtitle', $lng->str('Recently changed'));
$page->assign('pagetitle', $lng->str('Recently changed'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');
