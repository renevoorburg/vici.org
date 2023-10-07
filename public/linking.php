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
Just a page with info on how to link to places / views.
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$text_en="
<p style=\"margin-top:0\">
Please feel invited to link to Vici.org.
As explained on this page, several methods are provided to link to specific views of the map .
It is also possible to integrate the map in your own site.
This can be done using the <a href=\"/widget.php\">widget</a>.
For other forms of use or reuse, <a href=\"/dataservices.php\">data services</a> are provided.
</p>

<h3>Linking to a specific object on the map</h3>

<p>
To link to a map with a given marker selected (highlighted) and centered, call the <span class=\"code\">/selectview.php</span> script and append the parameter '<span class=\"code\">focus</span>' with the numerical identifier of the location (marker).
The identifier of a location is the numerical part of the the url of its page.
So <span class=\"code\"><a href='http://vici.org/selectview.php?focus=7859'>http://vici.org/selectview.php?focus=7859</a></span> links to a map with the Porta Negra (<span class=\"code\"><a href='http://vici.org/vici/7859'>http://vici.org/vici/7859</a></span>) centered. <br/>
A '<span class=\"code\">zoom</span>' parameter may be supplied.
Optionally, current day map labels can set to be either shown or hidden by setting the '<span class=\"code\">labels</span>' parameter.</p>

<p>Some more examples of how to link to a specific object displayed on the map:</p>

<p><span class=\"code\"><a href='http://vici.org/selectview.php?focus=7863'>http://vici.org/selectview.php?focus=7863</a></span><br />
Shows the Hercules Tower of A Coruña (<span class=\"code\"><a href=\"http://vici.org/vici/7863\">http://vici.org/vici/7863</a></span>). Zoomlevel is chosen automatically.</p>

<p><span class=\"code\"><a href='http://vici.org/selectview.php?focus=4255&zoom=15&labels=1'>http://vici.org/selectview.php?focus=4255&zoom=15&labels=1</a></span><br />
Shows the Colosseum of Rome (<span class=\"code\"><a href=\"http://vici.org/vici/4255\">http://vici.org/vici/4255</a></span>), zoomed in to a level to display the Forum Romanum. Current day map labels are shown.</p>

<h3>Linking to an area on the map</h3>
<p>You can link to a specific area on the map by providing the coordinates of the center point and a zoom level to the <span class=\"code\">/selectview.php</span> script.
Optionally labels on the map can be displayed. </p>

<p>Some examples of how to link to a specific map view:</p>

<p><span class=\"code\"><a href='http://vici.org/selectview.php?center=55.012333,-2.337620&zoom=10'>http://vici.org/selectview.php?center=55.012333,-2.337620&zoom=10</a></span><br />
Links to a map with location 55.012333, -2.337620 (latitude, longitude) and zoom factor 10.
Labels on the map are not shown.
This example gives an overview of the Hadrian wall.</p>

<p><span class=\"code\"><a href='http://vici.org/selectview.php?center=50.520889,8.725101&zoom=6&labels=1'>http://vici.org/selectview.php?center=50.520889,8.725101&zoom=6&labels=1</a></span><br />
This example links to the area of the German limes, zoom factor 6, labels on the map are shown.</p>
";

$text_nl="
<p>
Het staat uiteraard vrij om links te maken naar Vici.org.
Zoals op deze pagina uitgelegd wordt kan er op een aantal manieren een link naar een specifiek deel van de kaart gemaakt worden.
Het is ook mogelijk om de kaart te integreren in een andere site. Dit kan met met de <a href=\"/widget.php\">widget</a>.
Voor andere vormen van gebruik of hergebruik van de data zijn <a href=\"/dataservices.php\">dataservices</a> beschikbaar.
</p>

<h3>Links naar een specifieke plaats op de kaart</h3>

<p>
Om te linken naar een marker of plaats naar keuze, te tonen midden op de kaart, verwijst u naar het '<span class=\"code\">/selectview.php</span>'-script en geeft u de parameter '<span class=\"code\">focus</span>' als waarde de numerieke identifier van de plaats.
De identifier van een plaats is het numerieke deel van de url van de bijbehorende pagina.
Zo geeft <span class=\"code\"><a href='http://vici.org/vici/7859'>http://vici.org/vici/7859</a></span> de pagina over de Porta Negra.
De identifier is hier dus <em>7859</em>.
De link <span class=\"code\"><a href='http://vici.org/selectview.php?focus=7859'>http://vici.org/selectview.php?focus=7859</a></span> zo naar een kaart met centraal daarop de Porta Negra.<br/>
Er wordt automatisch een schaal gekozen die afhankelijk is van de aard van het object.
Optioneel kan er een '<span class=\"code\">zoom</span>'-waarde opgegeven worden om zo de schaal vast te leggen.
Ook is het mogelijk om aan te geven of de kaart de hedendaagse plaatsnamen en infrastructuur moet tonen.
Dit kan met de parameter '<span class=\"code\">labels</span>'.</p>

<p>Hier volgen nog een paar voorbeelden van links naar een kaart met in het midden een geselecteerd object:</p>

<p><span class=\"code\"><a href='http://vici.org/selectview.php?focus=7863'>http://vici.org/selectview.php?focus=7863</a></span><br />
Toont de Herculestoren van A Coruña. De schaal wordt automatisch gekozen. Hedendaagse labels worden niet getoond.</p>

<p><span class=\"code\"><a href='http://vici.org/selectview.php?focus=4255&zoom=15&labels=1'>http://vici.org/selectview.php?focus=4255&zoom=15&labels=1</a></span><br />
Toont een kaart met het colosseum van Rome centraal. De schaal is hier met '<span class=\"code\">zoom</span>' zo gekozen dat het Forum Romanum getoond wordt.
Ook de hedendaagse aanduidingen worden getoond ('<span class=\"code\">labels=1</span>').</p>

<h3>Links naar een specifiek punt op de kaart</h3>
<p>Een andere manier om naar een kaart te linken is door de coordinaten van het centrale punt samen met de schaal door te geven aan het '<span class=\"code\">/selectview.php</span>'-script. Ook hier kan gekozen worden of de hedendaagse kaartlabels getoond moeten worden.</p>

<p>Een paar voorbeelden van links naar een specifiek gedeelte van de kaart:</p>

<p>
<span class=\"code\"><a href='http://vici.org/selectview.php?center=55.012333,-2.337620&zoom=10'>http://vici.org/selectview.php?center=55.012333,-2.337620&zoom=10</a></span><br />
Linkt naar een kaart met in het midden het punt 55.012333, -2.337620 (breedtegraad, lengtegraad) en als schaal zoomfactor '10'.
Hedendaagse kaartlabels worden niet getoond.
Dit specifieke voorbeeld geeft een overzicht van de Hadrian wall.
</p>

<p>
<span class=\"code\"><a href='http://vici.org/selectview.php?center=50.520889,8.725101&zoom=6&labels=1'>http://vici.org/selectview.php?center=50.520889,8.725101&zoom=6&labels=1</a></span><br />
Dit voorbeeld geeft een kaart waarop het Germaanse deel van de limes goed in beeld is.
Hedendaagse plaatsaanduidingen worden getoond.</p>
";

switch ($lng->getLang()) {
   case 'en': 
        $text=$text_en;
        break;
   case 'nl': 
        $text=$text_nl;
        break;
   default:
       $text = '<span lang="en">'.$text_en.'</span>';
}

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('content', $text);
$page->assign('pagetitle', $lng->str('Linking to the map'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');
