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
Just a page "About Vici.org".
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$text_en='

<div style="float:right;width:560px;margin:0 0 4px 8px">
<iframe width="560" height="315" src="//www.youtube.com/embed/9LDdsN4_WRM" frameborder="0" allowfullscreen></iframe>
Presenting Vici.org at Wikidata trifft Archäologie (Berlin, 2013)
</div>

<p style="margin-top:0">Vici.org is the archaeological atlas of classical antiquity. It is a community driven archaeological map, inspired by and modelled after Wikipedia.</p>

<p>
The first version of Vici.org went online in May 2012.
It was preceded by a sister website <a href="http://omnesviae.org">Omnesviae.org</a>, a roman routeplanner based on the Peutinger map.
Since its start, Vici.org has grown a lot. At the time to this writing, over 140 contributors have added nearly 20,000 locations, approximately 1,000 line tracings and over 3,000 images.
</p>

<h2>Open Data</h2>

<p>
Similar to Wikipedia, all written content is available for reuse using the <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.nl">Creative Commons Attribution-ShareAlike</a>-license.
Metadata is available using the <a href="http://creativecommons.org/about/cc0">CC0 / Public Domain</a> dedication.
Images or line tracings may be available under other licenses.
Vici.org invites everyone to participate and share their knowledge of classical antiquity.
Vici.org does provide various services to reuse this shared knowledge, through various <a href="/dataservices.php">dataservices</a> or by using the <a href="/widget.php">Vici widget</a>.
</p>

<p>
<span style="font-style:italic">René Voorburg, December 2014.</span><br />
rene@digitopia.nl
</p>

';

$text_nl='<p>Vici.org is de archeologische atlas van de oudheid.</p>

<h2>Vici</h2>

<div style="float:right;width:560px;margin:0 0 4px 8px">
<iframe width="560" height="315" src="//www.youtube.com/embed/9LDdsN4_WRM" frameborder="0" allowfullscreen></iframe>
Presentatie van Vici.org voor Wikidata trifft Archäologie (Berlijn, 2013)
</div>

<p>De naam <em>Vici</em> verwijst naar het meervoud het woord <em>vicus</em> (dorp) in het latijn.
<em>Vici</em> betekent dus \'dorpen\', of vrijer \'plaatsen\'.
Volgens hedendaagse inzichten zou <em>vici</em> in het klassieke Latijn uitgesproken worden als \'wiki\', een bewuste verwijzing naar <em>Wikipedia</em> dat als voorbeeld diende.
Dat de term \'vici\' vooral bekend is van de woorden van Julius Caesar, "Veni, vidi, vici" (ik kwam, ik zag, ik overwon) is een leuke bijkomstigheid, "Ik kwam, ik zag, Vici!"</p>

<p>Begin 2011 is het idee voor Vici.org ontstaan. Via een zijsprong leidde dit eerst tot de Romeinse routeplanner <a href="http://omnesviae.org">Omnesviae.org</a>.
De eerste versie van Vici.org ging in mei 2012 online.
Sindsdien zijn functionaliteit en inhoud flink uitgebreid.
Ondertussen bevat Vici.org bijna 20.000 locaties, zo\'n 1.000 lijnen als wegen of aquaducten en meer dan 3.000 afbeeldingen, dit alles bijgedragen door meer dan 140 personen.</p>


<h2>Open Data</h2>

<p>Vici.org is, net als Wikipedia, gericht op <em>delen</em> en <em>samenwerken</em>. Zoals bij Wikipedia is alle geschreven content beschikbaar onder de <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.nl">Creative Commons Naamsvermelding/Gelijk delen</a>-licentie.
Metadata is vrij beschikbaar (<a href="http://creativecommons.org/about/cc0">CC0 / Public Domain</a>). Voor afbeeldingen en lijnen kunnen afwijkende licenties gelden.
Iedereen wordt uitgenodigd om via Vici.org zijn of haar kennis van de klassieke oudheid te delen.
Vici.org stelt gedeelde kennis op haar beurt ter beschikking voor hergebruik via verschillende <a href="/dataservices.php">dataservices</a> of via de <a href="/widget.php">Vici-widget</a>.</p>


<p><span style="font-style:italic">René Voorburg, 8 december 2014.</span><br />
rene@digitopia.nl</p>';

switch ($lng->getLang()) {
   case 'en': 
        $text = $text_en;
        break;
   case 'nl': 
        $text = $text_nl;
        break;
   default:
        $text = '<span lang="en">'.$text_en.'</span>';
}

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('content', $text);
$page->assign('pagetitle', $lng->str('About Vici'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');

?>