<?php

/**
Copyright 2013-6, René Voorburg, rene@digitopia.nl

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
Shows a selector page for adding new objects.
*/

require_once dirname(__FILE__).'/include/classLang.php';
require_once dirname(__FILE__).'/include/classSession.php';
require_once dirname(__FILE__).'/include/classViciCommon.php';
require_once dirname(__FILE__).'/include/classSiteKinds.php';
require_once dirname(__FILE__).'/include/classPage.php';

$lng = new Lang();
$session = new Session($lng->getLang());
$siteKinds = new SiteKinds($lng);

function getItemHTML(Lang $lngObj, $itemId, $itemName, $image)
{
    $explain = $lngObj->str($itemName . " explained");
    $siteKind = $lngObj->str($itemName);
    $lf = "\n";

    $ret = '';
    $ret .= '<div style="display:inline-block; width:260px; margin-right:20px; margin-bottom:16px">'.$lf;
    $ret .= '<div class="nearMarkerBox">'.$lf;
    $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/edit.php?new=' . $itemId) . '"><img title="'.$siteKind.'" class="icon' . $itemId . ' marker" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="></a>'.$lf;
    $ret .= '</div>'.$lf;
    $ret .= '<div class="nearTextBoxFull">'.$lf;
    $ret .= '<h3><a href="' . $lngObj->langURL($lngObj->getLang(), '/edit.php?new=' . $itemId) . '">' . $siteKind . '</a></h3>';
    $ret .= '<div style="position:relative;width:220px;height:124px">'.$lf;
    $ret .= '<a href="' . $lngObj->langURL($lngObj->getLang(), '/edit.php?new=' . $itemId) . '"><img style="position:absolute;top:0;right:0;left:0" src="//images.vici.org/crop/w220xh124/'.$image.'"></a>'.$lf;

    if ($explain) {
        $ret .= '<p style="position:absolute;left:0;right:0;bottom:0;margin:0;padding:4px;color:black;background-image:url(/images/white_75.png)">' . $explain . '</p>'.$lf;
    }
    $ret .= '</div>'.$lf;
    $ret .= '</div>'.$lf;
    $ret .= '</div>'.$lf;

    return $ret;
}

$choice = '';

$choice .= '<h2>'.$lng->str('Settlements').'</h2>';

$choice .= getItemHTML($lng, 3, $siteKinds->getName(3), 'uploads/cut.jpg');
$choice .= getItemHTML($lng, 9, $siteKinds->getName(9), 'uploads/vicus_micia_031.jpg');
$choice .= getItemHTML($lng, 14, $siteKinds->getName(14), 'uploads/wijster.png');

$choice .= '<h2>'.$lng->str('Military buildings').'</h2>';

$choice .= getItemHTML($lng, 4, $siteKinds->getName(4), 'uploads/portchester_castle_02.jpg');
$choice .= getItemHTML($lng, 15, $siteKinds->getName(15), 'uploads/2012_20_0126.jpg');
$choice .= getItemHTML($lng, 25, $siteKinds->getName(25), 'uploads/dsc_6439.jpg');

$choice .= '<h2>'.$lng->str('Infrastructural').'</h2>';

$choice .= getItemHTML($lng, 1, $siteKinds->getName(1), 'uploads/Aqueduct_de_les_Ferreres.jpg');
$choice .= getItemHTML($lng, 23, $siteKinds->getName(23), 'uploads/22671093.jpg');
$choice .= getItemHTML($lng, 22, $siteKinds->getName(22), 'uploads/Alcantara_Bridge.jpg');

$choice .= '<h2>'.$lng->str('Roman buildings').'</h2>';

$choice .= getItemHTML($lng, 13, $siteKinds->getName(13), 'uploads/9290725317_90c5c45a56_h.jpg');
$choice .= getItemHTML($lng, 7, $siteKinds->getName(7), 'uploads/Herberg_Nijmegen.jpg');
$choice .= getItemHTML($lng, 12, $siteKinds->getName(12), 'uploads/Merida_Roman_Theatre.jpg');
$choice .= getItemHTML($lng, 2, $siteKinds->getName(2), 'uploads/baths_of_diocletian_antmoose1.jpg');
$choice .= getItemHTML($lng, 11, $siteKinds->getName(11), 'uploads/Baalbek_Temple_of_Bacchus.jpg');
$choice .= getItemHTML($lng, 6, $siteKinds->getName(6), 'uploads/2011_16_0008.jpg');
$choice .= getItemHTML($lng, 5, $siteKinds->getName(5), 'uploads/img_2163_fotor.jpg');
$choice .= getItemHTML($lng, 21, $siteKinds->getName(21), 'uploads/Trier_Porta-Negra.jpg');

$choice .= '<h2>'.$lng->str('Smaller objects').'</h2>';

$choice .= getItemHTML($lng, 16, $siteKinds->getName(16), 'uploads/aphrodisias_sebasteion02.jpg');
$choice .= getItemHTML($lng, 20, $siteKinds->getName(20), 'uploads/roemischer_meilenstein_juelich_2009.jpg');
$choice .= getItemHTML($lng, 10, $siteKinds->getName(10), 'uploads/de_meern1_2.png');
$choice .= getItemHTML($lng, 17, $siteKinds->getName(17), 'uploads/40d_4215.jpg');
$choice .= getItemHTML($lng, 18, $siteKinds->getName(18), 'uploads/dsc_6634.jpg');

$choice .= '<h2>'.$lng->str('Current day objects or locations').'</h2>';

$choice .= getItemHTML($lng, 24, $siteKinds->getName(24), 'uploads/cesar_sa_mort.png');
$choice .= getItemHTML($lng, 8, $siteKinds->getName(8), 'uploads/museum_het_valkhof_nijmegen_netherlands_9566990543_.jpg');
$choice .= getItemHTML($lng, 19, $siteKinds->getName(19), 'uploads/Nieuw_Wulven2.jpg');

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('content', $choice);
$page->assign('sitesubtitle', $lng->str('New object'));
$page->assign('pagetitle', $lng->str('New object'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');

