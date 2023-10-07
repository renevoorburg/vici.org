<?php
if (!headers_sent()) { header('Content-Type:application/json; charset=UTF-8'); } ;

/* gets the data from a URL */
function get_data($url) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}


echo(get_data('http://api.geonames.org/findNearbyPlaceNameJSON?username=vici&lat='.$_GET['lat'].'&lng='.$_GET['lng']));

# http://api.geonames.org/findNearbyPlaceNameJSON?username=vici&lat=39.930801&lng=-75.160217

# http://omnesvici.dev/include/geoNamesFindNearbyPlaceNameJSON.php?lat=39.930801&lng=-75.160217


?>