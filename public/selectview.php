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
    Selectview.php offers an URL interface to specific views of the map.
    It sets required cookies -as requested by URL parameters - and redirects to the main page.
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');
require_once (dirname(__FILE__).'/include/classViciCommonLogic.php');

$lngObj = new Lang();
$db = new DBConnector(); // no error handling here ...

$validParamsFound = false; // redirect to / will follow if validParamFound (and cookies set)

if (isset($_GET['focus'])) {
    // example: /selectview.php?focus=Fectio
    $id = ViciCommonLogic::getSiteId($db, $_GET['focus'], $lngObj->getLang());
    $sql = "SELECT pnt_lat, pnt_lng FROM points WHERE pnt_id='$id'";
    $result = $db->query($sql); // ... error handling ...
    list($lat, $lng) = $result->fetch_row();
    
    setcookie('focus', $id, 0, '/');
    
    if (isset($_GET['center']) && isset($_GET['zoom'])) {
        // ?center=55.012333,-2.337620&zoom=10
        $coords = explode(",", $_GET['center']);


        header("Location: https://vici.org/#" . $_GET['zoom'] . "/" . $coords[0] . "," . $coords[1] . "/" . $id);
        die();

//        setcookie('center', $coords[0].','.$coords[1], 0, '/');
//        setcookie('zoom', $_GET['zoom'], 0, '/');
    } else {
//        setcookie ("center", $lat.','.$lng, 0, '/');
        if (isset($_GET['zoom'])) {
//          setcookie('zoom', $_GET['zoom'], 0, '/');
            header("Location: https://vici.org/#" . $_GET['zoom'] . "/" . $lat . "," . $lng . "/" . $id);
            die();


        } else {
            // setcookie ("zoom", "", time()-60000); // delete cookie
            header("Location: https://vici.org/#14/" . $lat . "," . $lng . "/" . $id);
            die();


        }
    }
    $validParamsFound=true;
}

if (!$validParamsFound && isset($_GET['center']) && isset($_GET['zoom'])) {
    // ?center=55.012333,-2.337620&zoom=10
    $coords = explode(",", $_GET['center']);
//    setcookie('center', $coords[0].','.$coords[1], 0, '/');
//    setcookie('zoom', $_GET['zoom'], 0, '/');
//    setcookie ("focus", "", time()-60000);  // delete cookie

    header("Location: https://vici.org/#" . $_GET['zoom'] . "/" . $coords[0] . "," . $coords[1]);
    die();

    $validParamsFound = true;
}


?>
<html>
<body>
<p>Error, no valid redirect given!<br/>
Some correct examples:<br/>
http://vici.org/selectview.php?center=55.012333,-2.337620&zoom=10<br/>
http://vici.org/selectview.php?center=55.012333,-2.337620&zoom=10&labels=1<br/>
http://vici.org/selectview.php?focus=Porta_Negra<br/>
http://vici.org/selectview.php?focus=Colosseum_Roma&zoom=15<br/>
http://vici.org/selectview.php?center=41.890179,12.492395&zoom=4&focus=Lvgdvno_capvt_Galliarvm<br/>
</p>
</body>
</html>
