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
Hides or unhides a point (toggle). JSON output.
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
    $sql = "SELECT pnt_hide FROM points WHERE pnt_id=".$id;
    $result = $db->query($sql);
    list($pnt_hide) = $result->fetch_row();

    if ($pnt_hide==0) {
        $pnt_hide_new = 1;
    } else {
        $pnt_hide_new = 0;
    }

    $sql = "UPDATE points SET pnt_hide=$pnt_hide_new WHERE pnt_id=$id";
    $result = $db->query($sql);

    echo "{ \"hidden\": \"$pnt_hide_new\" }";

} catch (Exception $e) {
    echo $e->getMessage();
}


