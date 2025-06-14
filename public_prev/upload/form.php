<?php

/**
Copyright 2013-4, René Voorburg, rene@digitopia.nl

This file is part of the Vici.org source as used on http://vici.org/

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
Form for uploading.
 */

$myDir = dirname(__FILE__);
require_once $myDir.'/../include/classLang.php';
require_once $myDir.'/../include/classSession.php';
require_once $myDir.'/../include/classViciCommon.php';
require_once $myDir.'/../include/interfaceUpload.php';
require_once $myDir.'/../include/classSite.php';
require_once $myDir.'/../include/classItemHTMLParts.php';
require_once $myDir.'/../include/classPage.php';

$lngObj = new Lang();
$session = new Session($lngObj->getLang());


$hasErrors = false;
$errMsg = '';
$formDisabled = false;
if (!$session->hasUser()) {
    $hasErrors = true;
    $errMsg = $lngObj->str('You need to log on');
    $formDisabled = true;
}

$id = isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["frm_id"]) ? $_POST["frm_id"] : 0);
$site = new Site($lngObj->getLang(), $id);
$dataArr = $_POST + $_GET + array('kind' => $site->getKind());

$format = isset($_GET["format"]) ? $_GET["format"] : "image";
switch ($format) {
    case "image":        
        require_once($myDir.'/../include/classUploadImage.php');
        $upload = new UploadImage($dataArr, $lngObj);
        $editmode = 'addimage';
        break;
    case "kml":
        require_once($myDir.'/../include/classUploadKML.php');
        $upload = new UploadKML($dataArr, $lngObj);
        $editmode = 'addkml';
        break;
    default:
        break;
}

// check preconditions:
$noErrors = isset($_SESSION['acc_id']) && $upload->authenticate($_SESSION['acc_id'], $id);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // data has been uploaded, validate and process:
    $noErrors = $upload->validate($_POST);
    if ($noErrors) { 

        $noErrors = $upload->process($_POST);
        $site->save($_SESSION['acc_id'], true);  // force update of metadata
    }
}

// determine page title and content:
$pagetitle = $noErrors ? $site->getName() : 'Error';

$mainText = ""; 
$lastMessage = $upload->getLastMessage();
if (! empty($lastMessage)) {
    $mainText .= '<p>'.$lastMessage.'</p>';
}
$mainText .= $upload->getPageForm();

// display page:
$page = new Page();

$page->assign('lang', $lngObj->getLang());
$page->assign('scripts', $upload->getPageScripts($lngObj->getLang()));
$page->assign('editmenu', ItemHTMLParts::editMenuHTML($editmode, $site->getId(), $lngObj));

if ($hasErrors) {
    $page->assign('errormsg', '<div class="errorbox">' . $errMsg . '</div>');
}

$page->assign('content', $mainText);
$page->assign('pagetitle', $pagetitle);
$page->assign('session', ViciCommon::sessionBox($lngObj, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lngObj));
$page->assign('finalscripts', '<script>initialize();</script>');

$page->display('content.tpl');
