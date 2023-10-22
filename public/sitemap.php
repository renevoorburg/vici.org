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
 * Creates a sitemap for Google and the like.
*/

require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');

$db = new DBConnector(); // no error handling here ...

if (!ob_start("ob_gzhandler")) ob_start();
if (!headers_sent()) { header('Content-Type:application/xml; charset=UTF-8'); } ;

$sql = "SELECT pnt_name, pmeta_edit_date, pnt_id
        FROM points LEFT JOIN pmetadata ON pnt_id = pmeta_pnt_id WHERE pnt_hide = 0 ORDER BY pnt_promote DESC, pmeta_edit_date DESC"; 
$result = $db->query($sql);

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

while ($row = $result->fetch_object()) {
    $name = $row->pnt_name;
    $urlname = "https://vici.org/vici/".$row->pnt_id."/";
    echo "<url>\n";
    echo "  <loc>".$urlname."</loc>\n";
    echo "  <xhtml:link rel='alternate' hreflang='en' href='".$urlname."?lang=en' />\n";
    echo "  <xhtml:link rel='alternate' hreflang='de' href='".$urlname."?lang=de' />\n";
    echo "  <xhtml:link rel='alternate' hreflang='fr' href='".$urlname."?lang=fr' />\n";
    echo "  <xhtml:link rel='alternate' hreflang='nl' href='".$urlname."?lang=nl' />\n";
    echo "  <lastmod>".substr($row->pmeta_edit_date, 0, 10)."</lastmod>\n";
    echo "</url>\n";
}

$sql = "select img_id, imgd_date from images left join img_data on img_id=imgd_imgid where img_hide=0";
$result = $db->query($sql);
while ($row = $result->fetch_object()) {
    $id = $row->img_id;
    $date = $row->imgd_date;
    echo "<url><loc>https://vici.org/image.php?id=".$id."</loc><lastmod>".substr($date, 0, 10)."</lastmod></url>\n";
}

echo '<url>'."\n";
echo "  <loc>https://vici.org/</loc>\n";
echo "  <xhtml:link rel='alternate' hreflang='en' href='https://vici.org/' />\n";
echo "  <xhtml:link rel='alternate' hreflang='de' href='https://vici.org/?lang=de' />\n";
echo "  <xhtml:link rel='alternate' hreflang='fr' href='https://vici.org/?lang=fr' />\n";
echo "  <xhtml:link rel='alternate' hreflang='nl' href='https://vici.org/?lang=nl' />\n";
echo "</url>\n";

echo '<url>'."\n";
echo "  <loc>https://vici.org/added.php</loc>\n";
echo "  <xhtml:link rel='alternate' hreflang='en' href='https://vici.org/added.php' />\n";
echo "  <xhtml:link rel='alternate' hreflang='de' href='https://vici.org/added.php?lang=de' />\n";
echo "  <xhtml:link rel='alternate' hreflang='fr' href='https://vici.org/added.php?lang=fr' />\n";
echo "  <xhtml:link rel='alternate' hreflang='nl' href='https://vici.org/added.php?lang=nl' />\n";
echo "</url>\n";
echo "</urlset>\n";
