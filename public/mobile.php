<?php
require_once dirname(__FILE__).'/include/classViciCommon.php';

if (! isset($_SERVER['HTTPS']) && ! ViciCommon::isTesting()) {
    header("Location: https://vici.org/mobile.php", true, 301);
    exit();
}
?>
<!doctype html>
<html>
<head>
    <title>Vici App</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=AIzaSyDZczR_KlcZRHPAHpxRlw9hTfs-H9Lvgbc"></script>
    <script type="text/javascript" src="/js/fastbutton.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/mobile.css" />
<?php if (preg_match("/\.dev$/i", $_SERVER['HTTP_HOST'])) { ?>
    <script type="text/javascript" src="/js/mobile.js"></script>
    <link rel="stylesheet" href="/css/add2home.css">
    <script type="application/javascript" src="/js/add2home.js"></script>
<?php } else { ?>
    <script type="text/javascript" src="/js/mobile.min.js"></script>
    <link rel="stylesheet" href="/css/add2home.min.css">
    <script type="application/javascript" src="/js/add2home.min.js"></script>
<?php } ?>
</head><body>
<div id="container">
    <div id="mapcanvas"></div>
    <div id="bbar">
        <div id="btnfocus" class="bbarbtn btnfocusinit"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" width="31" height="30"></div> 
        <div id="btnall" class="bbarbtn btnfont btnallon">Alles</div>
        <div id="btnvisual" class="bbarbtn btnfont btnvisualoff">Zichtbaar</div>
        <div id="btnlabels" class="bbarbtn btnlabelsoff"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" width="31" height="30"></div>
        <div id="btninfo" class="bbarbtn btninfooff"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" width="31" height="30"></div>
    </div>
    <div id="infobox"></div>
    <div id="page">
        <div class="content">
            <div id="defaultimage"></div>
            <div id="text"></div>
            <div id="copyright" class="copyright">CC-SA</div>
            <div id="otherlang"></div>
            <div id="moreimages"></div>
            <div id="popup"></div>
        </div>
        <div class="tbar">
            <h1 id="ttitle"></h1>
            <div id="back" class="backbtn"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" width="58" height="26"></div>
            <div id="backtxt" class="backbtn btnfont"></div>
        </div>
    </div>
</div>
</body></html>