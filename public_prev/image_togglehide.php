<?php

/**
Hides or unhides an image (toggle). JSON output.
 */

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommonLogic.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$db = new DBConnector(); // no errorhandling ...

if (!ob_start("ob_gzhandler")) { ob_start(); }
if (!headers_sent()) { header('Content-Type:application/json; charset=UTF-8'); }

if ($_SESSION['acc_level'] < 4 ) { die("Permission denied");}; // TODO this should go through class Session

try {
    $id = ViciCommonLogic::getSiteId($db, $_GET['id'], $lng->getLang());
    $sql = "SELECT img_hide FROM images WHERE img_id=".$id;
    $result = $db->query($sql);
    list($img_hide) = $result->fetch_row();

    if ($img_hide==0) {
        $img_hide_new = 1;
    } else {
        $img_hide_new = 0;
    }

    $sql = "UPDATE images SET img_hide=$img_hide_new WHERE img_id=$id";
    $result = $db->query($sql);

    echo "{ \"hidden\": \"$img_hide_new\" }";

} catch (Exception $e) {
    echo $e->getMessage();
}


