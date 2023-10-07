<?php

/* what, small, big, zindex */
// it's in the db now too....
$pkinds[1] = array("aquaduct", 4, 8, 3);
$pkinds[2] = array("baths", 9, 13, 3);
$pkinds[3] = array("city", 3, 6, 9);
$pkinds[4] = array("fort", 6, 9, 6);
$pkinds[5] = array("graves", 9, 13, 4);
$pkinds[6] = array("industry", 9, 11, 3);
$pkinds[7] = array("mansio", 9, 12, 4);
$pkinds[8] = array("museum", 7, 13, 4);
$pkinds[9] = array("vicus", 5, 10, 5);
$pkinds[10]= array("shipwreck", 9, 13, 3);
$pkinds[11]= array("temple", 6, 9, 3);
$pkinds[12]= array("theater", 7, 11, 4);
$pkinds[13]= array("villa", 6, 10, 6);
$pkinds[14]= array("rural", 10, 12, 2);
$pkinds[15]= array("watchtower", 8, 11, 3);
$pkinds[16]= array("altar", 9, 13, 3);
$pkinds[17]= array("object", 9, 13, 2);
$pkinds[18]= array("observation", 12, 17, 2);
$pkinds[19]= array("memorial", 9, 12, 3);
$pkinds[20]= array("milestone", 8, 11, 2);
$pkinds[21]= array("building", 9, 13, 3);
$pkinds[22]= array("bridge", 6, 9, 8);
// 22: special was romeinenfestival
$pkinds[23]= array("road", 6, 11, 8);
$pkinds[24]= array("event", 3, 10, 10);
$pkinds[25]= array("camp", 8, 11, 11);

$accuracy[0]= "on spot";
$accuracy[1]= "0 - 5 m.";
$accuracy[2]= "5 - 25 m.";
$accuracy[3]= "25 - 100 m.";
$accuracy[4]= "100 - 500 m.";
$accuracy[5]= "> 500 m.";

$url_base="/vici/";

// we need $_SESSION['acc_level'], so set it, even when no one has logged in:
if(empty($_SESSION['acc_id'])) {
    $_SESSION['acc_level'] = 0;
}

function my_urlencode($url) {
    $url = preg_replace('/ /', '_', $url);
    return(preg_replace('/%2F/', '/', urlencode($url)));
};

function my_stripslashes($strVar) {
    if (get_magic_quotes_gpc()) $strVar = stripslashes($strVar);
    return $strVar;
}

function returnUserInfo () 
// returns formatted info on loggin in user plus supporting links
{
    $ret="";
    if(empty($_SESSION['acc_id']))
    {
        $_SESSION['return']=$_SERVER['REQUEST_URI'];
        $ret.='<span style="margin-right:8px; color:#888;"><img  style="vertical-align:top;" src="/images/user-icon.png" />';      
        $ret.=sbabelfish("not logged on");
        $ret.='</span><a href="/login.php">';
        $ret.=sbabelfish('login/register'); 
        $ret.='</a>';
    } else { 
        $ret.='<span style="margin-right:8px"><img  style="vertical-align:top;" src="/images/user-icon.png" />';
        $ret.=htmlentities($_SESSION['acc_name']); 
        $ret.='</span><a href="/logout.php">';
        $ret.=sbabelfish('logout');
        $ret.='</a>';
    };
    return ($ret);
};

function returnMainMenu ()
// returns the formatted main menu
{
    global $chosenlang;
    $lang=$chosenlang;

    $ret="";
    $ret.='<ul style="list-style: none; margin:0px; padding: 0px;">';
    $ret.='<li><a href="'.getLangUrl($lang, '/').'">';
    $ret.=sbabelfish('Main page');
    $ret.="</a></li>\n";    
    $ret.='<li style="margin-top:12px"><a href="'.getLangUrl($lang, '/new.php').'">';
    $ret.=sbabelfish('Add a new place');
    $ret.="</a></li>\n";
    
    $ret.='<li><a href="'.getLangUrl($lang, '/added.php').'">';
    $ret.=sbabelfish('Recently added');
    $ret.="</a></li>\n";
    
    $ret.='<li><a href="'.getLangUrl($lang, '/changed.php').'">';
    $ret.=sbabelfish('Recently changed');
    $ret.="</a></li>\n";

    $ret.='<li style="margin-top:12px"><a href="'.getLangUrl($lang, '/linking.php').'">';
    $ret.=sbabelfish('Linking to the map');
    $ret.="</a></li>\n";
    $ret.='<li><a href="'.getLangUrl($lang, '/widget.php').'">';
    $ret.=sbabelfish('Vici widget');
    $ret.="</a></li>\n";
    $ret.='<li><a href="'.getLangUrl($lang, '/dataservices.php').'">';
    $ret.=sbabelfish('Data services');
    $ret.="</a></li>\n";
    
    $ret.='<li style="margin-top:12px"><a href="/mobile.php">';
    $ret.=sbabelfish('Mobile version');
    $ret.="</a></li>\n";
    
    $ret.='<li style="margin-top:12px"><a href="'.getLangUrl($lang, '/about-vici.php').'">';
    $ret.=sbabelfish('About Vici');
    $ret.="</a></li>\n";
    
    $ret.="</ul>\n";
    
    $ret.='<p style="margin:0px; padding-top:12px; padding-bottom:0px; color:#888">';
    $ret.=sbabelfish('Languages');
    $ret.="</p>\n";
    $ret.='<ul  style="list-style: none; padding: 0px; margin-left:12px; margin-top:0px; line-height:1.4em">';
    if ($lang!="de") { $ret.= "<li><a href='".getLangURL('de')."'>Deutsch</a></li>\n"; };
    if ($lang!="en") { $ret.= "<li><a href='".getLangURL('en')."'>English</a></li>\n"; };
    if ($lang!="fr") { $ret.= "<li><a href='".getLangURL('fr')."'>Français</a></li>\n"; };
    if ($lang!="nl") { $ret.= "<li><a href='".getLangURL('nl')."'>Nederlands</a></li>\n"; };
    $ret.="</ul>\n";
    
    $ret.='<div style="margin-top:40px">';
    
    $ret.="<a href='http://facebook.com/vici.org'><img src='/images/facebook.png'></a> <a href='https://twitter.com/OmnesViae'><img src='/images/twitter.png'></a>";
    
    //$ret.='<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fvici.org&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe>';
    $ret.='</div>';

    return ($ret);
};


function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}


?>