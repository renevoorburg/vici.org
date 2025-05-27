<?php

/**
 * Displays details of a given item. Provides edit functionality.
 */

require_once __DIR__ . '/include/classLang.php';
require_once __DIR__ . '/include/classSession.php';
require_once __DIR__ . '/include/classViciCommonLogic.php';
require_once __DIR__ . '/include/classViciRequest.php';
require_once __DIR__ . '/include/classDBConnector.php';
require_once __DIR__ . '/include/classSite.php';
require_once __DIR__ . '/include/classSiteKinds.php';
require_once __DIR__ . '/include/classIntlAnnotations.php';
require_once __DIR__ . '/include/classItemHTMLParts.php';
require_once __DIR__ . '/include/classPage.php';
require_once __DIR__ . '/include/classRDF.php';
require_once __DIR__ . '/include/classLineData.php';
require_once __DIR__ . '/include/classViciKML.php';

function enforceLogin() {
    $uri = $_SERVER['REQUEST_URI'];
    header('Location: /login.php?loginrequired&return=' . urlencode($uri));
}

$lngObj = new Lang();
$session = new Session($lngObj->getLang());
$session->enforceAnonymousRateLimit();

$requestKindStr = ViciCommonLogic::matchRequestedContentType($_GET['id']);
switch ($requestKindStr) {
    case 'rdf':
        if (!$session->hasUser()) {
            enforceLogin();
        }
        $rdfObj = new RDF('site', $_GET['id']);
        exit;
        break;
    case 'kml':
        if (!$session->hasUser()) {
            enforceLogin();
        }
        $kml = new ViciKML($_GET['id']);
        exit;
        break;
}

$db = new DBConnector();

try {
    $site = new Site($lngObj->getLang(), ViciCommonLogic::getSiteId($db, $_GET['id'], $lngObj->getLang()));
} catch (Exception $e) {
    ViciCommon::terminateWith404();
}

$siteKinds = new SiteKinds($lngObj);

//
$nearby = ItemHTMLParts::getNearbyHTML($db, $site, $lngObj);
$museums = ItemHTMLParts::getRelevantMuseumsHTML($db, $site, $lngObj);
$thisPlace = ItemHTMLParts::getMetadataHTML($db, $site, $lngObj);

$extScripts = viciCommon::jqueryInclude();
$extScripts .= <<<EOD
<link rel="stylesheet" href="/js/ol/v4.6.5/css/ol.css" type="text/css">
<script src="/js/ol/v4.6.5/ol.js"></script>
<script src="/js/vici.js"></script>
<link rel="stylesheet" href="/js/photoswipe/photoswipe.css">
<link rel="stylesheet" href="/js/photoswipe/default-skin/default-skin.css">
<link rel="stylesheet" href="/css/webfont.css">
<script src="/js/photoswipe/photoswipe.min.js"></script>
<script src="/js/photoswipe/photoswipe-ui-default.min.js"></script>
EOD;

$viciCall = "<script type=\"text/javascript\">
$(document).ready(function() {
    var mapObj = new ViciWidget('canvas',
     {  defaultMap: \"OSM\",
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
        },".ViciCommon::getBaseUrlDeclaration()."
        ".ViciCommon::getViciTokenDeclaration()."
        showFilter: true,
        highlights: 0,
        lang: \"".$lngObj->getLang()."\",
        center: { lat: " . $site->getLat() . ", lng: " . $site->getLng() . "},
        followFocus: true,
        focus: " . $site->getId() . ",
        showScale: \"metric\"
     }
    );

    $('.langSel').click(function(event) {
        $('.article').hide();
        $('#tx'+event.target.id).show();

        $('.langSelLi').removeClass('selected');
        $('#x'+event.target.id).addClass('selected');

        });
    
    $('#myIcon').click(function(e) {mapObj.panTo(".$site->getId().")});
        
});
</script>";

//
$lf = "\n";
$panReq = "
<script>
var gallery;
var pswpElement = document.querySelectorAll('.pswp')[0];
var pswpoptions = {
    bgOpacity: 1.0,
    shareEl: false,
    history: false
};

var onThumbnailsClick = function(e) {
    e = e || window.event;
    e.preventDefault ? e.preventDefault() : e.returnValue = false;
    var eTarget = e.target || e.srcElement;
    pswpoptions.index = parseInt(eTarget.getAttribute('data-pswp-uid'));    
    gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, pswpitemsObject, pswpoptions);
    gallery.init();
}

var galleryElements = document.querySelectorAll('figure.item img');

for(var i = 0, l = galleryElements.length; i < l; i++) {
    galleryElements[i].setAttribute('data-pswp-uid', i);
    galleryElements[i].onclick = onThumbnailsClick;
}

</script>" . $lf;

// item images
$itemImages = '';
//$itemImages .= '<h2 style="margin-top:0">' . $lngObj->str('Images') . '</h2>' . $lf;
$itemImages .= ItemHTMLParts::getItemImagesHTML($site, $lngObj) . $lf;


$nearbyImages = ItemHTMLParts::getNearbyImages($db, $site->getLat(), $site->getLng(), 'https://images.vici.org/crop/w175xh175', 9, 30, $lngObj, $site->getId());
if (strlen($nearbyImages) > 1 ) {
    $itemImages .= '<h3>' . $lngObj->str('Surroundings') . ': </h3>' .  $lf;
    $itemImages .= '<div id="nearby">' .$nearbyImages.'</div>';
}

// content
$intlAnnotations = new IntlAnnotations($db, $site, $lngObj);
$annotation = $intlAnnotations->getAnnotations();
$langList = $intlAnnotations->getLanguageSelector();

// tracing info
$linefoot = '';
$tracing = new LineData($site->getId());
if ($tracing->getNumLines()) {
    //$annotation .= '<h2>'.$lngObj->str('Tracing').'</h2>'.$lf;
    $linefoot .= sprintf($lngObj->str('Tracing by %s.') . ' ', $tracing->getAuthor());
    if ($tracing->isFree()) {
        if (!$tracing->isPublicDomain()) {
            $linefoot .= sprintf($lngObj->str('Available as %s under a %s license.') . ' ',
                '<a href="/vici/' . $site->getId() . '/kml">KML</a>', $tracing->getLicense());
            $linefoot .= $tracing->getAttribution() ? ' ' . $tracing->getAttribution() . '.' : '';
        }
    } else {
        $linefoot .= $tracing->getLicense();
        $linefoot .= $tracing->getAttribution() ? ' - ' . $tracing->getAttribution() . '. ' : '';
        //$linefoot .= sprintf($lngObj->str('Simplified representation available as %s.'), '<a href="/vici/'.$site->getId().'/kml">KML</a>');
    }
    //$linefoot .= '</p>';
}

// footer
$footer = '<p>' ;
$footer.= sprintf($lngObj->str('This object was added by %s on %s.'),  $site->getCreatorName(), $site->getCreateDate()). " ";
$footer.= sprintf($lngObj->str('Last update by %s on %s.'),  $site->getEditorName(), $site->getEditDate())." ";
$footer.= $lngObj->str('Persistent URI').': http://vici.org/vici/'.$site->getId().' . '.$lngObj->str('Download as').' <a href="/vici/'.$site->getId().'/rdf">RDF/XML</a>, <a href="/vici/'.$site->getId().'/kml">KML</a>.<br>';
$footer.= $lngObj->str('Annotation CCBYSA');
$footer.= $lngObj->str('Metadata CC0')."<br>";
$footer.= $linefoot;
$footer.= "</p>";

// metadata
$metalinks = '    <meta itemprop="datePublished" content="' . $site->getCreateDate() . '">' . "\n";
$metalinks .= '    <meta itemprop="dateModified" content="' . $site->getEditDate() . '">' . "\n";

$metalinks .= '    <link rel="canonical" href="https://vici.org/vici/' . $site->getId() . '/"/>' . "\n";
foreach ($lngObj->getAvailableLanguages() as $lang) {
    $metalinks .= '    <link rel="alternate" hreflang="' . $lang . '" href="https://vici.org/vici/' . $site->getId() . '/?lang=' . $lang . '"/>' . "\n";
}
$metalinks .= '    <link rel="alternate" type="application/rdf+xml" href="http://vici.org/vici/' . $site->getId() . '/rdf" />';

// display page:
$page = new Page();

$page->assign('editmenu', ItemHTMLParts::editMenuHTML('view', $site->getId(), $lngObj));

$page->assign('scripts', $extScripts . $viciCall);
$page->assign('finalscripts', $panReq);
//$page->assign('thisplace', $thisPlace);
//$page->assign('nearby', $nearby);
$page->assign('lang', $lngObj->getLang());
$page->assign('langsellist', $langList);
$page->assign('sitesubtitle', viciCommon::htmlentitiesVici($site->getTitle()));
$page->assign('description', viciCommon::htmlentitiesVici($site->getSummary()));
$page->assign('headmeta', $metalinks);
$page->assign('metadata', $thisPlace);
$page->assign('content', viciCommon::link_urls($annotation).'<br>'.$museums.$nearby);
$page->assign('annotatie', $lngObj->str('Annotation'));
$page->assign('pagetitle', viciCommon::htmlentitiesVici($site->getTitle()));
$page->assign('session', ViciCommon::sessionBox($lngObj, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lngObj));
$page->assign('itemimages', $itemImages);

$page->assign('footer', $footer);

$page->display('item.tpl');
