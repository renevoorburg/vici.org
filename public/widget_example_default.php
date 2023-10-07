<!DOCTYPE html>
<html lang="en">
<head>
    <title>Vici.org Widget Implementation Example</title>
    <meta charset="UTF-8" />
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
    <?php if (preg_match("/\.dev$/i", $_SERVER['HTTP_HOST'])) {
        echo '<script src="/js/jquery-1.8.3.min.js" type="text/javascript"></script>',"\n";
        echo '<script type="text/javascript" src="/js/vici-1.3.1.js"></script>';
    } else {
        echo '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" ></script>',"\n";
        echo '    <script type="text/javascript" src="http://vici.org/js/vici-1.3.1.min.js"></script>';
    } ?>
</head>

<body>
<h1>Vici.org Widget Implementation Example</h1>

<div id="map" style="width: 640px; height:480px;">here the map!</div>

<script type="text/javascript">

var mapObj = new ViciWidget(document.getElementById('map'), {} );

</script>


<pre>var mapObj = new ViciWidget(document.getElementById('map'), {} );</pre>

</body>
</html>