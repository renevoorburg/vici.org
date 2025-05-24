<?php

/**
Shows the big map. Homepage.
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$extScripts = viciCommon::jqueryInclude();
$extScripts .=<<<EOD
<link rel="stylesheet" href="/js/ol/v4.6.5/css/ol.css" type="text/css">
<script src="/js/ol/v4.6.5/ol.js"></script>
<script src="/js/vici.js"></script>
EOD;
  
$viciCall = "<script type=\"text/javascript\">

    $(document).ready(function() {
        var mapObj = new ViciWidget('canvas',
            {   defaultMap: \"OSM\",
                useMaps: [\"AWMC\", \"OSM\", \"DARE\", \"ESRI\"],
                extraMaps: {
                    DARE: {
                        name: 'Digital Atlas of the Roman Empire',
                        url: \"https://tiles.vici.org/imperium/{z}/{x}/{y}.png\",
                        attributions: '© <a href=\"http://dare.ht.lu.se/\">Johan Åhlfeldt</a>',
                        maxZoom: 11
                    },
                    ESRI: {
                        name: 'Esri WorldImagery',
                        url: \"https://tiles.vici.org/world/{z}/{y}/{x}\",
                        attributions: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                    }
                },
                extraOverlays: {
                    LIMESNL: {
                        name: 'Limes NL',
                        url: \"https://tiles.vici.org/Limes/{z}/{x}/{y}.png\",
                        attributions: '© Olav Odé - CC BY',
                        opacity: 0.8
                    }
                },
                ".ViciCommon::getBaseUrlDeclaration()."
                ".ViciCommon::getViciTokenDeclaration()."
                showFilter: true,
//                filter: { visibility: \"anyVisibility\", era: \"anyEra\" }, // onlyVisible , contemporaryEra, historicalEra
                highlights: 6,
                lang: \"".$lng->getLang()."\",
                setUrl: true,
                showScale: \"metric\",
                moveHere: true
            }
        );
    });

</script>";

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('sitesub', $lng->str('Roman history nearby'));
$page->assign('sitesubtitle', $lng->str('Roman history nearby'));
$page->assign('description', $lng->str('metadescription'));
$page->assign('scripts', $extScripts.$viciCall);
$page->assign('pagetitle', $lng->str('Roman history nearby').':');
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('home.tpl');
