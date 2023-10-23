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
Just a page to show how to implement the widget.
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$libs = "<div style=\"font-family: Consolas, Menlo, Monaco, 'Lucida Console', 'Liberation Mono', 'DejaVu Sans Mono', 'Bitstream Vera Sans Mono', 'Courier New', monospace, serif; font-size: 13px;\">";
$libs .= htmlspecialchars('<link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">').'<br />';
$libs .= htmlspecialchars('<script src="https://openlayers.org/en/v4.6.5/build/ol.js"></script>').'<br />';
$libs .= htmlspecialchars('<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>').'<br />';
$libs .= htmlspecialchars('<script src="https://vici.org/js/vici.js"></script>').'<br />';
$libs .= '</div>';



$element  = "<div style=\"font-family: Consolas, Menlo, Monaco, 'Lucida Console', 'Liberation Mono', 'DejaVu Sans Mono', 'Bitstream Vera Sans Mono', 'Courier New', monospace, serif; font-size: 13px;\">";
$element .= htmlspecialchars('<div id="map" style="width:600px; height:400px; position:relative"></div>').'<br />';
$element .= '</div>';

$example = "<div style=\"font-family: Consolas, Menlo, Monaco, 'Lucida Console', 'Liberation Mono', 'DejaVu Sans Mono', 'Bitstream Vera Sans Mono', 'Courier New', monospace, serif; font-size: 13px;\">";
$example .= htmlspecialchars('<script type="text/javascript">').'<br />';
$example .= htmlspecialchars('var mapObj = new ViciWidget(\'map\', {});').'<br />';
$example .= htmlspecialchars('</script>').'<br />';
$example .= '</div>';

$apitable  = "<table>\n";
$apitable .= "<thead><tr><th scope=\"col\">Parameter</th><th scope=\"col\">Function&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th scope=\"col\">Usage</th></tr></thead>\n";
$apitable .= "<tbody>\n";
$apitable .= "<tr><td class='code'>\"zoom\"</td><td>Initial zoomlevel.</td><td>Integer: values <span class='code'>5</span> to <span class='code'>18</span>. Defaults to <span class='code'>10</span><br/>If user changes the zoom of the map, the new zoom be kept (using a cookie) and used next time the map is displayed.</td></tr>\n";
$apitable .= "<tr><td class='code'>\"center\"</td><td>Initial center of the map.</td><td>Object containing values for lattitude <span class='code'>\"lat\"</span> and longitude <span class='code'>\"lng\"</span>: values <span class='code'>5</span> to <span class='code'>18</span>. For example <span class='code'>{ \"lat\": 50.84, \"lng\": 5.69}</span>. If none given, the previous location is used (stored in a cookie) or otherwise the center of Rome.<br/></td></tr>\n";
$apitable .= "<tr><td class='code'>\"mapTypeId\"</td><td>Map background</td><td>Possible values: <span class='code'>\"SATELLITE\"</span> (default), <span class='code'>\"HYBRID\"</span> or  <span class='code'>\"HYBRID\"</span> for the <a href=\"http://imperium.ahlfeldt.se/\">Digital Atlas of the Roman Empire (DARE) map</a>.</td></tr>\n";

$apitable .= "<tr><td class='code'>\"lang\"</td><td>Language of the interface</td><td>A string containing a two letter languagecode. Supported values are <span class='code'>\"de\"</span>, <span class='code'>\"en\"</span>, <span class='code'>\"fr\"</span>, <span class='code'>\"nl\"</span>. Defaults to the language setting of the webbrowser, with a fallback to English (<span class='code'>\"en\"</span>).</td></tr>\n";

$apitable .= "<tr><td class='code'>\"highlights\"</td><td>Featured locations</td><td>An integer value that sets the maximum number of locations that are given extra attention by showing them in a 'featured' box. For example, the value <span class='code'>2</span> will highlight upto two locations. Defaults to <span class='code'>0</span>, meaning no locations are highlighted.</td></tr>\n";

$apitable .= "<tr><td class='code'>\"showPrefbox\"</td><td>User customizable views</td><td>A boolean, the value <span class='code'>true</span> will show a clickable menu in the lower left corner that allows the user to select what objects are shown on the map and what map to use. Default value is <span class='code'>false</span> (no menu).</td></tr>\n";

$apitable .= "<tr><td class='code'>\"preferences\"</td><td>Customizable views</td><td>Using the <span class='code'>preferences</span> object one can define what is shown on the map. These setting can be altered by the user using the preferences menu. So when the preference menu is disabled (<span class='code'>showPrefbox</span> set to <span class='code'>false</span>) these setting cannot be overridden by the user. Two aspects can be controlled, the kind of objects to be shown, archeological or touristic (the <span class='code'>\"era\"</span> property, possible values are <span class='code'>\"onlyContemporary\"</span>, <span class='code'>\"RomanAndContemporary\"</span> and <span class='code'>\"onlyRoman\"</span>) and whether invisible objects should displayed or not (the <span class='code'>\"visibility\"</span> property, possible values are <span class='code'>\"onlyVisible\"</span> and <span class='code'>\"anyVisibility\"</span>).</td></tr>\n";

$apitable  .= "</tbody>\n";
$apitable .= "</table>\n";


$text_en="<p>
The Vici.org map is available as a javascript widget and can be included on any web page. Just follow three steps.</p>

<h2>1. Include the required libraries</h2>
<p>The Vici widget requires a stylesheet and three libraries to run. These are OpenLayers, jQuery and of course the Vici library. Include these libraries in your  page by adding the following code between the <span class=\"code\">&lt;head&gt;</span>-tags of the page:<br />".$libs."<br />

<h2>2. Determine where the map should be shown</h2>
<p>Reserve a place on the page for the map by adding a <span class=\"code\">&lt;div&gt;</span>-tag with the desired layout. Give this element an <span class=\"code\">id</span>, for example <span claslans=\"code\">\"map\"</span>. Here is an example:<br />".$element."<br />

<h2>3. Activate the widget</h2>

<p>Activate the widget by putting the following code on the bottom of the page, just before the <span class=\"code\">&lt;/body&gt;</span>-tag:<br />
".$example."<br/>
Use the <span class=\"code\">id</span> of the element you want the map to be shown. See a <a href='https://voorburg.home.xs4all.nl/vici/widget_example_default.html'>live demo</a> of this example.</p>

<p>The widget can be customized in many ways. See for example the implementation by <a href=\"http://livius.org/\">Livius</a>. Please <a href=\"/about-vici.php\">contact</a> for more instructions.</p>";

$text_nl="<p>
De kaart van Vici.org kan als een javascript widget op iedere website geplaatst worden. Dit kan in drie eenvoudige stappen.</p>

<h2>1. Laad de benodige biblotheken</h2>
<p>De Vici-widget heeft een stylesheet en drie bibliotheken nodig om te kunnen functioneren. De bibliotheken zijn OpenLayers, JQuery en de Vic-widget zelf. Laad deze bibliotheken in door de volgende code tussen de <span class=\"code\">&lt;head&gt;</span>-tags van je pagina te zetten:<br />".$libs."<br />

<h2>2. Bepaal waar de kaart op de pagina moet komen</h2>
<p>Zet op de gewenste plek op de pagina een <span class=\"code\">&lt;div&gt;</span>-tag met het gewenste formaat. Geeft dit element een <span class=\"code\">id</span> mee, bijvoorbeeld <span class=\"code\">\"map\"</span>. Bijvoorbeeld zoals hier gedaan:<br />".$element."<br />

<h2>3. Activeer de widget</h2>

<p>Activeer de widget door de volgende code onderaan de pagina te plaatsen, net voor de <span class=\"code\">&lt;/body&gt;</span>-tag:<br />
".$example. "<br/>Gebruik hier het <span class=\"code\">id</span> het van element waarin de kaart getoond moet worden. <a href='https://voorburg.home.xs4all.nl/vici/widget_example_default.html'>Bekijk een demonstratie</a> van dit voorbeeld.</p>


<p>Op eenvoudige wijze kan de werking van de widget aangepast worden, zie hiervoor bijvoorbeeld <a href='http://www.livius.org/'>Livius.org</a>. <a href=\"/about-vici.php\">Neem even contact met me op</a>, dan kan ik je daarbij helpen.</p>";

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
$page->assign('pagetitle', $lng->str('Vici widget'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');

?>