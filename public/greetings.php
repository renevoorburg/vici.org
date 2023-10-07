<?php

include("include/getlang.php");
include("include/vici-common.php");


if ($_GET['n']) {
    $n = (int)$_GET['n'];
} else {
    $n = 150;
};

if ($_GET['s']) {
    $s = (int)$_GET['s'];
} else {
    $s = 10;
};


?>
<!DOCTYPE html>
<html lang="<?php echo $chosenlang; ?>">
<head>
<title>Vici.org - Presentation</title>
<meta charset="UTF-8" />

<style>
a:link {color: #ffffff}
a:visited {text-decoration:none; color: #ffffff}
a:hover{text-decoration:underline; color: #ffffff}
a:active{text-decoration:underline; color: #ffffff}
</style>

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="http://www.panoramio.com/wapi/wapi.js?v=1&hl='<?php echo $chosenlang ?>'"></script>
<script type="text/javascript">

n = <?php echo $n; ?>;
s = <?php echo $s; ?>;

function requestAroundLatLong(lat, lng, km)
{
   // angle per km = 360 / (2 * pi * 6378) = 0.0089833458
   var angle=km* 0.0089833458;
   var myRequest = new panoramio.PhotoRequest({
      'rect': {'sw': {'lat': Number(lat)-angle, 'lng': Number(lng)-angle}, 'ne': {'lat': Number(lat)+angle, 'lng': Number(lng)+angle}}
      });
   return myRequest;
}


function getCountry(lat, lng) 
{
    if (window.XMLHttpRequest) { geoCall=new XMLHttpRequest(); } else if (window.ActiveXObject) { geoCall=new ActiveXObject("Microsoft.XMLHTTP"); };
    geoCall.onreadystatechange=function() {	
        if (geoCall.readyState==4) {
            if (geoCall.status == 200) {
                geoResponse = eval( "(" + geoCall.responseText + ")" ); 
                //alert(geoResponse.geonames[0].countryName);
                //return (geoResponse.geonames[0].countryName) ;
                document.getElementById('textbox').innerHTML = geoResponse.geonames[0].countryName+", "+geoResponse.geonames[0].name+"<br />"+document.getElementById('textbox').innerHTML;
            }
        }
    }
    //alert("http://api.geonames.org/findNearbyJSON?lat="+lat+"&lng="+lng+"&username=vici");
    geoCall.open("GET","/include/geoNamesFindNearbyPlaceNameJSON.php?lat="+lat+"&lng="+lng,true);
    geoCall.send(null);        
}

function showNextSite() 
{
    lat = Number(response.features[current].geometry.coordinates[1]);
    lng = Number(response.features[current].geometry.coordinates[0]);
    
    map.panTo(new google.maps.LatLng(lat, lng));
    var countryName = getCountry(lat, lng);
    
    document.getElementById('textbox').innerHTML = '<strong>'+response.features[current].properties.title+'</strong><br />'+response.features[current].properties.summary;
    
    document.getElementById('icon').src = '/images/'+response.features[current].properties.kind+'.png';
    
    leftWidget.setRequest(requestAroundLatLong(lat, lng, 0.3));
    leftWidget.setPosition(0);
    
    rightWidget.setRequest(requestAroundLatLong(lat, lng, 0.3));
    rightWidget.setPosition(3);
    
    //getCountry(lat, lng);
    
    current++;
    if (current==n) loadSites();
};


function loadSites() 
{
    
    if (window.XMLHttpRequest) { jsonPoints=new XMLHttpRequest(); }	else if (window.ActiveXObject) { jsonPoints=new ActiveXObject("Microsoft.XMLHTTP"); };
    jsonPoints.onreadystatechange=function() {	
        if (jsonPoints.readyState==4) {
            if (jsonPoints.status == 200) {
                response = eval( "(" + jsonPoints.responseText + ")" ); 
                current=0;
                if (typeof leftWidget == 'undefined') {
                    var panoramioOptions = {
                        'width': 220,
                        'height': 600,
                        'position' : 'left',
                        'columns': 1,
                        'rows': 3,
                        'disableDefaultEvents': true,
                        'croppedPhotos': true
                    };
                    leftWidget = new panoramio.PhotoListWidget(document.getElementById('left'), requestAroundLatLong(response.features[current].geometry.coordinates[1], response.features[current].geometry.coordinates[0], 0.5), panoramioOptions);
                    leftWidget.setPosition(0);
                    leftWidget.enableNextArrow(false);
                    leftWidget.enablePreviousArrow(false);
                    
                    rightWidget = new panoramio.PhotoListWidget(document.getElementById('right'), requestAroundLatLong(response.features[current].geometry.coordinates[1], response.features[current].geometry.coordinates[0], 0.5), panoramioOptions);
                    rightWidget.setPosition(3);
                    rightWidget.enableNextArrow(false);
                    rightWidget.enablePreviousArrow(false);
                    
                    showNextSite();
                }
            }
        }
    }
    jsonPoints.open("GET","/random.php?n="+n,true);
    jsonPoints.send(null);

}


function initialize() 
{
    
    // create map
	var mapOptions = {
		center: new google.maps.LatLng(50.84,5.69),
    	zoom: 17,
    	minZoom: 4,
        streetViewControl: false,
        mapTypeControl: false,
        panControl: false,
        rotateControl: false,
        scaleControl: false,
        zoomControl: false,
        mapTypeId: google.maps.MapTypeId.SATELLITE
    };
    
    // global vars:
    map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
    
    loadSites();
    
    setInterval(function(){showNextSite()}, s*1000);

};

</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-31682789-1']);
  _gaq.push(['_setDomainName', 'vici.org']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</head>
<body onload="initialize();">

<div id="map_canvas" style="position:absolute; left:220px; right:220px; top:0px; bottom:0px">test</div>

<div id="textwrap" style="position: absolute; top: 8px; width: 100%; height: 0px"><div style="min-height: 30px; max-height: 100px; position: relative; width: 400px; margin: 0px auto; color: #ffffff; font-family: Helvetica; font-size: 12px; background-image: url(/images/black_50.png); padding: 8px 25px 8px 8px;"><img id="icon" src="/images/fort.png" style="position:absolute;" /><div id="textbox" style="margin-left:40px"></div> </div></div>

<div id="left" style="position: absolute; top: 0px; left:0px; width:220px; bottom:0px; background-color: #fff; padding-top:8px;"></div>
<div id="right" style="position: absolute; top: 0px; right:0px; width:220px; bottom:0px; background-color: #fff; padding-top:8px;"></div>

<div id="bottomwrap" style="position: absolute; bottom: 20px; width: 100%; height: 0px"><div style="position: relative; width: 400px; margin: 0px auto; "><div style="position:absolute; bottom:0px; left:0px; right:0px; min-height:40px; color: #ffffff; font-family: Helvetica; font-size: 14px; background-image: url(/images/black_50.png); padding: 8px 25px 8px 8px;"><strong>Looking back at a magnificent past... <br/><br/>Thanks for your support for <a href="http://vici.org/">Vici.org</a> in 2012 and best wishes for 2013!</strong></div></div></div>


</body>
</html>