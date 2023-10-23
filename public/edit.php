<?php

/**
 * Page for editing a new or existing site.
 */

require_once dirname(__FILE__) . '/include/classLang.php';
require_once dirname(__FILE__) . '/include/classSession.php';
require_once dirname(__FILE__) . '/include/classViciCommonLogic.php';
require_once dirname(__FILE__) . '/include/classSite.php';
require_once dirname(__FILE__) . '/include/classSiteKinds.php';
require_once dirname(__FILE__) . '/include/classItemHTMLParts.php';
require_once dirname(__FILE__) . '/include/classPage.php';

$lngObj = new Lang();
$lang = $lngObj->getLang();

$session = new Session($lang);
$db = new DBConnector();

$hasErrors = false;
$errMsg = '';
$dbErrMsg = '';
$formDisabled = false;
if (!$session->hasUser()) {
    $hasErrors = true;
    $errMsg = $lngObj->str('Error: You need to log on.<br>');
    $formDisabled = true;
}


$dataPosted = !empty($_POST["editmode"]);

if ($dataPosted) {
    $id = isset($_POST["id"]) ? (int)$_POST["id"] : null;
} else {
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : null;
}


$site = new Site($lang, $id);
$siteKinds = new SiteKinds($lngObj);

if ($dataPosted) {
    // fill $site object with posted data
    // $site->setChangeNote(my_stripslashes($postArr["note"]));
    $site->setCoords($_POST["coords_frm"]);
    $site->setIsVisible($_POST["visibilityselect"]);
    $site->setKind($_POST["kindselect"]);
    //$site->setKindSpecifier(my_stripslashes($_POST["specifier"]));
    $site->setLocationAccuracy($_POST["accuracyselect"]);
    for ($i = 0; $i < 10; $i++) {
        $extIdsArr[$i] = ViciCommon::stripslashesVici((string)$_POST["extid" . $i]);
    }
    $site->setExtIdsObj(ItemHTMLParts::extIdPostArr($_POST));

    $site->setSummary(ViciCommon::stripslashesVici($_POST["short_edit"]));
    $site->setAnnotation(ViciCommon::stripslashesVici($_POST["edit_full"]));
    $site->setTitle(ViciCommon::stripslashesVici($_POST["kop_edit"]));

    $site->setStartYear($_POST["start_yr"]);
    $site->setEndYear($_POST["end_yr"]);

    $dbErrMsg = $site->getLastError();

    if (empty($dbErrMsg)) {

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';

        if ($site->save($_SESSION['acc_id'])) {
            header('Location: '.$lngObj->langURL($lang, $protocol.'://' . $_SERVER['HTTP_HOST'] . '/vici/' . $site->getId() .'/'));
        } else {
            // no error except for a save error, form was double clicked?
            // redirect to home...
            header('Location: '.$lngObj->langURL($lang,$protocol.'://' . $_SERVER['HTTP_HOST'] . '/'));
        }
        exit;
    } else {
        $hasErrors = true;
    }
}

if (!empty($_GET["new"])) {

    $editmode = 'new';
    $site->setKind((int)$_GET['new']);

} else {
    $editmode = 'edit';
}

$extIds= $site->getExtIdsObj();


// scripts

$scripts = viciCommon::jqueryInclude();
$scripts .= '
    <script src="/vendor/ckeditor/ckeditor/ckeditor.js"></script>
    <link rel="stylesheet" href="/js/ol/v4.6.5/css/ol.css" type="text/css">
    <script src="/js/ol/v4.6.5/ol.js"></script>
    <script src="/js/edit.js"></script>
    ';


$finalscripts = '
    <script>
        editPage();
        
        var session = JSON.parse(sessionStorage.getItem("session"));
    ';

if ($editmode === "edit") {
    $finalscripts .= '
        session.center = {lat: ' . $site->getLat() . ', lng: ' . $site->getLng() . '};
        ';
}

$finalscripts .= '
    document.getElementById("coords_frm").value =  session.center.lat + ", " + session.center.lng;
    var mapObj = new MapWidget("editcanvas", "coords_frm", session.center.lng, session.center.lat, ' . $site->getKind() . ' );

    document.getElementById("coords_frm").addEventListener("blur", function() { mapObj.moveMarker(this.value)});
    document.getElementById("kindselect").addEventListener("change", function() { mapObj.setMarkerIcon(this.options[this.selectedIndex].value)});

    var kindselect = document.getElementById("kindselect");
    var periodbox = document.getElementById("periodbox");
         
    kindselect.addEventListener("change", function(){
        if (this.value == 8 || kindselect.value == 18 || this.value == 19 ) {
            periodbox.style.display = "none";
        } else {
            periodbox.style.display = "block";
        }
    });
        
    if (kindselect.value == 8 || kindselect.value == 18 || kindselect.value == 19 ) {
        periodbox.style.display = "none";
    } else {
        periodbox.style.display = "block";
    }
    </script>
    ';

// display page:
$page = new Page();

//ViciCommon::editMenu($page, $site, $lngObj, 'edit');

$page->assign('editmenu', ItemHTMLParts::editMenuHTML($editmode, $site->getId(), $lngObj));

$page->assign('lang', $lngObj->getLang());
$page->assign('session', ViciCommon::sessionBox($lngObj, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lngObj));

$page->assign('scripts', $scripts);
$page->assign('finalscripts', $finalscripts);

$page->assign('pagetitle',
    $lngObj->str($editmode . '_head') . ' (' . $lngObj->str($lngObj->getLang() . '_' . $lngObj->getLang()) . ')');

// form labels:
$page->assign('locationlabel', $lngObj->str('Location') . ':');
$page->assign('summarylabel', $lngObj->str('Summary') . ':');
$page->assign('categorylabel', $lngObj->str('Category') . ':');
$page->assign('visibilitylabel', $lngObj->str('Visibility') . ':');
$page->assign('tagslabel', $lngObj->str('Tags') . ':');

//form fields:
$page->assign('editmode', $editmode);
$page->assign('disabled', $formDisabled);

if ($hasErrors) {
    $page->assign('errormsg', '<div class="errorbox">' . $errMsg . $dbErrMsg . '</div>');
}

$page->assign('pntid', $id);
$page->assign('name', viciCommon::htmlentitiesVici($site->getTitle()));
$page->assign('summary', viciCommon::htmlentitiesVici($site->getSummary()));
$page->assign('annotation', $site->getAnnotation());

//$page->assign('coords', $site->getLat() . ', ' . $site->getLng());

$page->assign('kindselectoptions', $siteKinds->optionList($site->getKind()));
$page->assign('accuracyselectoptions', ItemHTMLParts::accuracyOptionList($site->getLocationAccuracy(), $lngObj));
$page->assign('accuracyselectoptions', ItemHTMLParts::accuracyOptionList($site->getLocationAccuracy(), $lngObj));

$page->assign('visibility', $site->getIsVisible());
$page->assign('visible', $lngObj->str('visible'));
$page->assign('invisible', $lngObj->str('invisible'));

$page->assign('accuracyselectoptions', ItemHTMLParts::accuracyOptionList($site->getLocationAccuracy(), $lngObj));



$page->assign('start', $site->getStartYearStr());
$page->assign('end',  $site->getEndYearStr() );


$page->assign('extids', ItemHTMLParts::extIdInputHTML($extIds->getAllUrlsArray()));

$page->assign('submit', $lngObj->str('Save'));

//
$page->display('edit.tpl');
