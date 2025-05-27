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

$text_en='<p style="margin-top:0">Vici.org is the archaeological atlas of classical antiquity. It is a community-driven archaeological map, inspired by and modelled after Wikipedia.</p>

<p>
The first version of Vici.org went online in May 2012.
It was preceded by a sister website, <a href="http://omnesviae.org">Omnesviae.org</a>, a Roman route planner based on the Peutinger map.
</p>

<h2>Open Data</h2>
<p>
Similar to Wikipedia, all written content is available for reuse under the <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.nl">Creative Commons Attribution-ShareAlike</a> license.
Metadata is available under the <a href="http://creativecommons.org/about/cc0">CC0 / Public Domain</a> dedication.
Images or line tracings may be available under other licenses.
Vici.org invites everyone to participate and share their knowledge of classical antiquity.
Vici.org provides various services to reuse this shared knowledge; please contact me for more information.
</p>

<p>
<span style="font-style:italic">René Voorburg, May 27th, 2025.</span><br />
rene@digitopia.nl
</p>
';

$text_nl='<p>Vici.org is de archeologische atlas van de oudheid.</p>

<h2>Vici</h2>


<p>De naam <em>Vici</em> verwijst naar het meervoud het woord <em>vicus</em> (dorp) in het latijn.
<em>Vici</em> betekent dus \'dorpen\', of vrijer \'plaatsen\'.
Volgens hedendaagse inzichten zou <em>vici</em> in het klassieke Latijn uitgesproken worden als \'wiki\', een bewuste verwijzing naar <em>Wikipedia</em> dat als voorbeeld diende.
Dat de term \'vici\' vooral bekend is van de woorden van Julius Caesar, "Veni, vidi, vici" (ik kwam, ik zag, ik overwon) is een leuke bijkomstigheid, "Ik kwam, ik zag, Vici!"</p>

<p>Begin 2011 is het idee voor Vici.org ontstaan. Via een zijsprong leidde dit eerst tot de Romeinse routeplanner <a href="http://omnesviae.org">Omnesviae.org</a>.
De eerste versie van Vici.org ging in mei 2012 online.</p>

<h2>Open Data</h2>

<p>Vici.org is, net als Wikipedia, gericht op <em>delen</em> en <em>samenwerken</em>. Zoals bij Wikipedia is alle geschreven content beschikbaar onder de <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.nl">Creative Commons Naamsvermelding/Gelijk delen</a>-licentie.
Metadata is vrij beschikbaar (<a href="http://creativecommons.org/about/cc0">CC0 / Public Domain</a>). Voor afbeeldingen en lijnen kunnen afwijkende licenties gelden.
Iedereen wordt uitgenodigd om via Vici.org zijn of haar kennis van de klassieke oudheid te delen.
Vici.org stelt gedeelde kennis op haar beurt ter beschikking voor hergebruik via verschillende dataservices. Neem hiervoor even contact met me op.</p>

<p><span style="font-style:italic">René Voorburg, 27 mei 2025.</span><br />
rene@digitopia.nl</p>';

$text_de = '<p style="margin-top:0">Vici.org ist der archäologische Atlas der klassischen Antike. Es handelt sich um eine gemeinschaftlich erstellte archäologische Karte, inspiriert von und modelliert nach Wikipedia.</p>

<p>
Die erste Version von Vici.org ging im Mai 2012 online.
Zuvor gab es eine Schwester-Website, <a href="http://omnesviae.org">Omnesviae.org</a>, einen römischen Routenplaner basierend auf der Peutingerkarte.
</p>

<h2>Offene Daten</h2>
<p>
Ähnlich wie bei Wikipedia sind alle schriftlichen Inhalte zur Wiederverwendung unter der <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.de">Creative Commons Attribution-ShareAlike</a>-Lizenz verfügbar.
Metadaten stehen unter der <a href="http://creativecommons.org/about/cc0">CC0 / Public Domain</a>-Widmung zur Verfügung.
Bilder oder Linienzeichnungen können unter anderen Lizenzen verfügbar sein.
Vici.org lädt alle ein, ihr Wissen über die klassische Antike zu teilen.
Vici.org bietet verschiedene Dienste zur Wiederverwendung dieses geteilten Wissens an; bitte kontaktieren Sie mich für weitere Informationen.
</p>

<p>
<span style="font-style:italic">René Voorburg, 27. Mai 2025.</span><br />
rene@digitopia.nl
</p>
';

$text_fr = '<p style="margin-top:0">Vici.org est l’atlas archéologique de l’Antiquité classique. Il s’agit d’une carte archéologique collaborative, inspirée et modélisée d’après Wikipédia.</p>

<p>
La première version de Vici.org a été mise en ligne en mai 2012.
Elle a été précédée par un site sœur, <a href="http://omnesviae.org">Omnesviae.org</a>, un planificateur d’itinéraires romains basé sur la carte de Peutinger.
</p>

<h2>Données ouvertes</h2>
<p>
Comme sur Wikipédia, tout le contenu écrit est disponible pour réutilisation sous la licence <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.fr">Creative Commons Attribution-ShareAlike</a>.
Les métadonnées sont disponibles sous la dédicace <a href="http://creativecommons.org/about/cc0">CC0 / Domaine public</a>.
Les images ou tracés peuvent être disponibles sous d’autres licences.
Vici.org invite chacun à participer et à partager ses connaissances sur l’Antiquité classique.
Vici.org propose divers services pour réutiliser ces connaissances partagées ; veuillez me contacter pour plus d’informations.
</p>

<p>
<span style="font-style:italic">René Voorburg, 27 mai 2025.</span><br />
rene@digitopia.nl
</p>
';

switch ($lng->getLang()) {
   case 'en': 
        $text = $text_en;
        break;
   case 'nl': 
        $text = $text_nl;
        break;
   case 'de': 
        $text = $text_de;
        break;
   case 'fr': 
        $text = $text_fr;
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