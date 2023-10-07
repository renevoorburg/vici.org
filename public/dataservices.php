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
Just a page with info on how to use data services.
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$text_en="
<p>All content on Vici.org is published under the Creative Commons Attribution-ShareAlike 3.0 (<a href='http://creativecommons.org/licenses/by-sa/3.0/'>CC BY-SA 3.0</a>) license. It may be shared, adapted or commercially used under the condition that a reference with link to <a href='https://vici.org/'>https://vici.org/</a>, or to the relevant page on Vici.org, as the source is provided. If you alter, transform, or build upon this work, you may distribute the resulting work only under the same or similar license to this one.</p> 

<h2>Obtaining data from Vici.org</h2>
<p>An API to obtain or use data from Vici.org is available. If you intend to use data from Vici.org, please <a href='/about-vici.php'>let me know</a> so that we can work out the best solution together.</p>

<h3>Multiple language support</h3>

<p>Vici.org supports annotations in multiple languages. Http content negotiation is used to determine the desired language. This can be overruled by supplying the 'lang' parameter. Supported languages are English (lang=en), Dutch (lang=nl), German (lang=de) and French (lang=fr). When an unknown language is requested, English is used as a default.<br /> 
Mind that the 'summary'-field might be returned in a language other than requested, when no summary is available in the requested language.</p>

<h3>Data calls</h3>

<p><a href='https://vici.org/geojson.php?bounds=38.0,-7.0,40.0,-5.0&zoom=11'>https://vici.org/geojson.php?bounds=38.0,-7.0,40.0,-5.0&zoom=11</a><br />
Returns all markers and lines for given bounds area in <a href='http://www.geojson.org/'>Geo-JSON</a> format.
The bounds parameters is specified as  atitude, longitude of south-west point, latitude, longitude of north-east point. The higher the value of 'zoom', the more 'detail' markers are returned.</p>

<a href='https://vici.org/vici/49/json'>https://vici.org/vici/49/json</a><br />
Returns the data for marker '49' in Geo-JSON format. For other serializations, try <a href='https://vici.org/vici/49/rdf<'>https://vici.org/vici/49/rdf</a> (RDF / Linked Data) or <a href='https://vici.org/vici/49/kml'>https://vici.org/vici/49/kml</a> (KML - Keyhole Markup Language).<br>
Adding a '&lang=en'-parameter will return the data in Dutch, regardless http content negotation.</p>

<h3>Cross-origin Resource Sharing</h3>
<p>The /geojson.php interface has Cross-origin Resource Sharing (CORS) enabled. Alternatively /geojson.php will return JSONP when a 'callback' parameter has been supplied.</p>";

$text_nl=$text_en;

switch ($lng->getLang()) {
   case 'en': 
        $text=$text_en;
        break;
   case 'nl': 
        $text=$text_nl;
        break;
   default: 
        $text=$text_en;
}

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('content', $text);
$page->assign('pagetitle', $lng->str('Data services'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');

?>