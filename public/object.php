<?php

/**
Copyright 2013-4, René Voorburg, rene@digitopia.nl

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
Returns JSON data for a given object.
 */

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classSiteData.php');

$lng = new Lang();
$session = new Session($lng->getLang());

if (!ob_start("ob_gzhandler")) ob_start();
if (!headers_sent()) { header('Content-Type:application/json; charset=UTF-8'); } ;

$site = new SiteData($lng->getLang(), (int)$_GET['id']);
$imgArr = $site->getImgArr();

echo "{\"type\": \"Feature\",\n";
echo " \"geometry\": {\"type\": \"Point\", \"coordinates\": [",$site->getLng(),", ",$site->getLat(),"]},\n";
echo " \"properties\":\n";
echo "   {\"id\": ",$site->getId(),",\n";
//echo "    \"url\": ", json_encode(ViciCommon::$url_base.ViciCommon::urlencodeVici($site->getName()).$lng->getLangGET('?')), ",\n";
echo "    \"url\": ", json_encode(ViciCommon::$url_base.$_GET['id'].$lng->getLangGET('?')), ",\n";
echo "    \"title\": ",json_encode($site->getTitle()),",\n";
echo "    \"kind\": ",json_encode(ViciCommon::$pkinds[$site->getKind()][0]),",\n";
echo "    \"kindId\": ",json_encode($site->getKind()),",\n";
//echo "    \"specifier\": ",json_encode($site->getKindSpecifier()),",\n";
echo "    \"isvisible\": ",json_encode($site->getIsVisible()),",\n";
echo "    \"accuracy\": ",$site->getLocationAccuracy(),",\n";
echo "    \"summary\": ",json_encode($site->getSummary()),",\n";
echo "    \"text\": ",json_encode(ViciCommon::cleanHtml($site->getAnnotation())),",\n";
echo "    \"altLang\": ",json_encode($site->getAltLang()),",\n";
echo "    \"altText\": ",json_encode(ViciCommon::cleanHtml($site->getAltText())),",\n";
echo "    \"extIds\": ",json_encode(''),",\n";
echo "    \"createDate\": ",json_encode($site->getCreateDate()),",\n";
echo "    \"creatorId\": ",json_encode($site->getCreatorId()),",\n";
echo "    \"creatorUser\": ",json_encode($site->getCreatorUserName()),",\n";
echo "    \"creatorName\": ",json_encode($site->getCreatorName()),",\n";
echo "    \"editDate\": ",json_encode($site->getEditDate()),",\n";
echo "    \"editorId\": ",json_encode($site->getEditorId()),",\n";
echo "    \"editorUser\": ",json_encode($site->getEditorUserName()),",\n";
//echo "    \"editorName\": ",json_encode($site->getEditorName()),"\n";

echo "    \"editorName\": ",json_encode($site->getEditorName()),",\n";
echo "    \"images\": [";
$sep = "{";
foreach ($imgArr as $imgData) {
    echo $sep;
    echo "\"id\": ", $imgData["id"],",\n";
    echo "      \"path\": ", json_encode($imgData["path"]),",\n";
    echo "      \"title\": ", json_encode($imgData["title"]),",\n";
    echo "      \"description\": ", json_encode($imgData["description"]),",\n";
    echo "      \"isdefault\": ", json_encode($imgData["default"]),",\n";
    echo "      \"uploader\": ", json_encode($imgData["uploader"]),",\n";
    echo "      \"ownwork\": ", json_encode($imgData["ownwork"]),",\n";
    echo "      \"creator\": ", json_encode($imgData["creator"]),",\n";
    echo "      \"attribution\": ", json_encode($imgData["attribution"]),",\n";
    echo "      \"date\": ", json_encode($imgData["date"]),",\n";
    echo "      \"license\": ", json_encode($imgData["license"]),"}";
    $sep = ",\n      {";
}
echo "\n   ]\n";
echo "  }\n";
echo "}";
