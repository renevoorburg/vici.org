<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 05-02-18
 * Time: 07:59
 */

require_once dirname(__FILE__).'/include/classLang.php';
require_once dirname(__FILE__).'/include/classSession.php';
require_once dirname(__FILE__).'/include/classViciCommon.php';
require_once dirname(__FILE__).'/include/classDBConnector.php';
require_once dirname(__FILE__).'/include/classItemHTMLParts.php';

$lng = new Lang();
$session = new Session($lng->getLang());
$db = new DBConnector(); // no errorhandling ...
set_time_limit (300);

if (isset($_GET['near'])) {
    $coords = explode(",", $_GET['near']);
    $exclude = isset($_GET['exclude']) ? intval($_GET['exclude']) : 0;
    echo "<html><head><meta charset='UTF-8'><style>body {margin:0;padding:0} img {padding:0 5px 5px 0;border:0}</style></head><body>";
    echo ItemHTMLParts::getNearbyImages($db, $coords[0], $coords[1], 'https://static.vici.org/cache/175x175-2', 9, 30, $lng, $exclude);
    echo "</body></html>";
}