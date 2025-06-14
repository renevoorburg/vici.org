<?php

/**
 * Page that displays an image together with its metadata (license, etc).
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.1 - 2015-09-15
 * @todo use classImage
 */



require_once(dirname(__FILE__) . '/include/classLang.php');
require_once(dirname(__FILE__) . '/include/classSession.php');
require_once(dirname(__FILE__) . '/include/classViciCommon.php');
require_once(dirname(__FILE__) . '/include/classDBConnector.php');
require_once(dirname(__FILE__) . '/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());
$session->enforceAnonymousRateLimit();
$db = new DBConnector();

$id = (int)$_GET['id'];

$sql = "select img_id, img_path, imgd_ownwork, imgd_creator, imgd_source, imgd_title, imgd_date, imgd_description, imgd_attribution, imgd_metadata, acc_realname, license_short, license_url, license_uploadable
from images
left join img_data on img_id=imgd_imgid
left join accounts on imgd_uploader=acc_id
left join licenses on imgd_license=license_id
where img_id=$id and img_hide=0";

$result = $db->query($sql);
$html = '';
if ($result->num_rows <> 1) {
   ViciCommon::terminateWith404();
}

$row = $result->fetch_object();
$path = $row->img_path;
$title = (isset($row->imgd_title)) ? $row->imgd_title : $lng->str('Untitled');

$html .= '<p><img src="//images.vici.org/size/w800'.$path.'"></p>';

if (isset($row->imgd_description)) {
    $html .= "<p>" .  preg_replace('@\n@', '<br>', $row->imgd_description) . "</p>";
}

$html .= "<table>";
if ($row->imgd_ownwork == 1) {
    $html .= "<tr><td>Creator: </td><td>" . $row->acc_realname . "</td></tr>";
} else {
    $html .= "<tr><td>Creator: </td><td>" . $row->imgd_creator . "</td></tr>";
    if (isset($row->imgd_source)) {
        $html .= "<tr><td>Source: </td><td><a href=\"" . $row->imgd_source . "\">" . $row->imgd_source . "</td></tr>";
    }
}

if ($row->license_uploadable == 1) {
    $html .= "<tr><td>License: </td><td><a href=\"" . $row->license_url . "\">" . $row->license_short . "</td></tr>";
}

if (isset($row->imgd_attribution)) {
    $html .= "<tr><td>Attribution: </td><td>" . $row->imgd_attribution . "</td></tr>";
}

$html .= "<tr><td>Added: </td><td>" . $row->imgd_date . "</td></tr>";
$html .= "<tr><td>Uploaded by: </td><td>" . $row->acc_realname . "</td></tr>";
if (isset($row->imgd_metadata)) {
    $html .= "<tr><td>EXIF data: </td><td>" . $row->imgd_metadata . "</td></tr>";
}

$html .= "</table>";

$html .= '<h2>' . $lng->str('Image used in') . '</h2>';
$result = $db->query("select pnt_name, pnt_id from pnt_img_lnk left join points on pil_pnt=pnt_id where pil_img=$id and pnt_hide=0");
$html .= '<ul>';
while ($row = $result->fetch_object()) {
    $html .= '<li><a href="'.$lng->langURL($lng->getLang(),'/vici/' . $row->pnt_id . '/').'">' . $row->pnt_name . '</a></li>';
}
$html .= '</ul>';

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('content', $html);
$page->assign('pagetitle', $title);
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');

?>
