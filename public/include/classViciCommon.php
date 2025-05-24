<?php

/**
 * Class ViciCommon. Common generic functions for Vici that don't require a db connection.
 */


require_once (dirname(__FILE__).'/classLang.php');
require_once (dirname(__FILE__).'/classSession.php');
require_once (dirname(__FILE__).'/classPage.php');

class ViciCommon 
{

    public static $url_base = "/vici/";

    public static $pkinds = array (
        1 => array("aquaduct", 4, 8, 3),
        2 => array("baths", 9, 13, 3),
        3 => array("city", 3, 6, 9),
        4 => array("fort", 6, 9, 6),
        5 => array("graves", 9, 13, 4),
        6 => array("industry", 9, 11, 3),
        7 => array("mansio", 9, 12, 4),
        8 => array("museum", 7, 13, 4),
        9 => array("vicus", 5, 10, 5),
        10 => array("shipwreck", 9, 13, 3),
        11 => array("temple", 6, 9, 3),
        12 => array("theater", 7, 11, 4),
        13 => array("villa", 6, 10, 6),
        14 => array("rural", 10, 12, 2),
        15 => array("watchtower", 8, 11, 3),
        16 => array("altar", 9, 13, 3),
        17 => array("object", 9, 13, 2),
        18 => array("observation", 12, 17, 2),
        19 => array("memorial", 9, 12, 3),
        20 => array("milestone", 8, 11, 2),
        21 => array("building", 9, 13, 3),
        22 => array("bridge", 6, 9, 8),
        23 => array("road", 6, 11, 8),
        24 => array("event", 3, 10, 10),
        25 => array("camp", 8, 11, 11),
    );

    public static function handlePreflightReq()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
          // return only the headers and not the content
          // only allow CORS if we're doing a GET - i.e. no saving for now.
          if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: X-Requested-With, X-Vici-Token');
          }
          exit;
        }   
    }
    
    public static function sendCORSHeaders($isJsonpReq)
    {
        if (!ob_start("ob_gzhandler")) ob_start();
        if (!headers_sent()) { 
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: X-Requested-With, X-Vici-Token');
            if ($isJsonpReq) {
                header('Content-Type:text/javascript; charset=UTF-8');
            } else {
                header('Content-Type:application/json; charset=UTF-8');
            } 
        }
    }

    public static function mainMenu(Lang $lngObj) {
        $lang = $lngObj->getLang();
    
        $ret= "";
        $ret.= '<ul style="list-style:none;margin:0;padding:0">';
        $ret.= '<li><a href= "'.$lngObj->langURL($lang, '/').'">';
        $ret.=  $lngObj->str('Main page');
        $ret.= "</a></li>\n";    

        $ret.= '<li style="margin-top:12px"><a href= "'.$lngObj->langURL($lang, '/new.php').'">';
        $ret.=  $lngObj->str('Add a new place');
        $ret.= "</a></li>\n";
    
        $ret.= '<li><a href="'.$lngObj->langURL($lang, '/added.php').'">';
        $ret.=  $lngObj->str('Recently added');
        $ret.= "</a></li>\n";
    
        $ret.= '<li><a href="'.$lngObj->langURL($lang, '/changed.php').'">';
        $ret.=  $lngObj->str('Recently changed');
        $ret.= "</a></li>\n";

        // $ret.= '<li><a href="'.$lngObj->langURL($lang, '/dataservices.php').'">';
        // $ret.=  $lngObj->str('Data services');
        // $ret.= "</a></li>\n";
    
        $ret.= '<li style="margin-top:12px"><a href="'.$lngObj->langURL($lang, '/about-vici.php').'">';
        $ret.=  $lngObj->str('About Vici');
        $ret.= "</a></li>\n";
        // $ret.= '<li><a href="'.$lngObj->langURL($lang, '/widget.php').'">';
        // $ret.=  $lngObj->str('Vici widget');
        // $ret.= "</a></li>\n";
    
        $ret.= "</ul>\n";

        $ret.= '<p style="margin:0;padding-top:12px;padding-bottom:0;color:#888">';
        $ret.=  $lngObj->str('Languages');
        $ret.= ":</p>\n";
        $ret.= '<ul  style="list-style:none;padding:0;margin-left:12px;margin-top:0;line-height:1.4em">';
        if ($lang!= "de") { $ret.=  "<li><a href= '".$lngObj->langURL('de')."'>Deutsch</a></li>\n"; };
        if ($lang!= "en") { $ret.=  "<li><a href= '".$lngObj->langURL('en')."'>English</a></li>\n"; };
        if ($lang!= "fr") { $ret.=  "<li><a href= '".$lngObj->langURL('fr')."'>Français</a></li>\n"; };
        if ($lang!= "nl") { $ret.=  "<li><a href= '".$lngObj->langURL('nl')."'>Nederlands</a></li>\n"; };
        $ret.= "</ul>\n";


        $ret.= '<p style="margin:0 0 8px 0;padding-top:12px;padding-bottom:0;color:#888">';
        $ret.=  $lngObj->str('Support Vici.org');
        $ret.= ":</p>\n";

        $ret.='<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="BBSUCN9SDNBNJ" />
<input type="image" src="/images/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
</form>';

        $ret.= '<p style="margin:0 0 8px 0;padding-top:12px;padding-bottom:0;color:#888">';
        $ret.=  $lngObj->str('Follow Vici.org');
        $ret.= ":</p>\n";
        $ret.= '<div style="margin-top:10px">';
        $ret.= "<a href='https://archaeo.social/@vici' rel='me'><img src='/images/mastodon.png' alt='@vici@archaeo.social'></a>";
        $ret.= '</div>';

        $ret.= '<p style="margin:0 0 8px 0;padding-top:12px;padding-bottom:0;color:#888">';
        $ret.=  $lngObj->str('Build Vici.org');
        $ret.= ":</p>\n";
        $ret.= '<div style="margin-top:10px">';
        $ret.= "<a href='https://github.com/renevoorburg/vici.org'><img src='/images/github-mark.png' width='50' height='50' alt='github'></a>";
        $ret.= '</div>';

//        $ret.= '<div style="position:relative;left:-10px;width:160px"><a class="twitter-timeline" href="https://twitter.com/OmnesViae" data-widget-id="524469139243417602">Tweets van @OmnesViae</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';

        return ($ret);
    }

    public static function urlencodeVici($url) {
        $url = preg_replace('/ /', '_', $url);
        return(preg_replace('/%2F/', '/', urlencode($url)));
    }

    public static function stripslashesVici($strVar) {
        if (get_magic_quotes_gpc()) $strVar = stripslashes($strVar);
        return $strVar;
    }

    public static function cleanHtml($text)
    {
        $ret = strip_tags($text,'<h1><h2><h3><p><br><div><blockquote><em><strong><ul><ol><li><a>');
        $ret = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $ret);   // remove style attributes
        $ret = preg_replace('/<div>/', '<p>', $ret);                  // replace div by p
        $ret = preg_replace('/<\/div>/', '</p>', $ret);               // replace div by p
        $ret = preg_replace('/\s+/', ' ', $ret);                      // remove double spaces
        $ret = preg_replace('/<p>\s*&nbsp;\s*<\/p>/', '', $ret);      // remove -a case of - empty lines
        $ret = preg_replace('/<p> /', '<p>', $ret);                   // some redundant spaces to remove
        return $ret;
    }

    public static function getBaseUrlDeclaration()
    {
        if (getenv('VICIBASE')) {
            return "
                baseUrl: '" . getenv('VICIBASE') . "',";
        } else {
            return "";
        }
    }

    public static function getSiteBase(): string
    {
        $site_base = "http://";
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") {
            $site_base = "https://";
        }
        return $site_base . $_SERVER["SERVER_NAME"];
    }

    public static function captchaCheck() : void
    {
        if (getenv('CAPTCHA_SEC')) {
            $response = $_POST["g-recaptcha-response"];
            $secret = getenv('CAPTCHA_SEC');
            $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}&remoteip={$_SERVER['REMOTE_ADDR']}");
            $captcha_success = json_decode($verify);

            if ($captcha_success->success == false) {
                echo "<p>You are a bot! Go away!</p>";
                exit;
            }
        }
    }

    public static function captchaInclude() : string
    {
        if  (getenv('CAPTCHA_SITE')) {
            return '<script src="https://www.google.com/recaptcha/api.js" async defer></script>'."\n";
        } else {
            return '';
        }
    }

    public static function captchaDisplay() : string
    {
        if  (getenv('CAPTCHA_SITE')) {
            return '<div style="margin-left:160px;margin-top:16px;" class="g-recaptcha" data-sitekey="' . getenv('CAPTCHA_SITE') . '"></div>'."\n";
        } else {
            return '';
        }
    }

    public static function htmlentitiesVici($str) {
        return htmlentities($str, ENT_QUOTES, "UTF-8");
    }

    public static function sessionBox (Lang $lngObj, Session $sessionObj)
        // returns formatted info on logged in user plus supporting links
    {
        $ret = "";
        if (!$sessionObj->getUserId()) {
            $sessionObj->setReturnURL($_SERVER['REQUEST_URI']);
            $ret.= '<span style="margin-right:8px;color:#888"><img  style="vertical-align:top" src="/images/user-icon.png" />';
            $ret.= $lngObj->str("not logged on");
            $ret.= '</span><a href="/login.php">';
            $ret.= $lngObj->str('login/register');
            $ret.= '</a>';
        } else {
            $ret.= '<span style="margin-right:8px"><img  style="vertical-align:top" src="/images/user-icon.png" />';
            $ret.= self::htmlentitiesVici(($sessionObj->getAccountName()));
            $ret.= '</span><a href="/logout.php">';
            $ret.= $lngObj->str('logout');
            $ret.= '</a>';
        }
        return ($ret);
    }

    public static function gen_uuid()
    {
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

    /**
     *
     * @param $text string
     * @return mixed the text with urls as anchored links
     */
    public static function link_urls($text) {
        // based on http://stackoverflow.com/questions/1188129/replace-urls-in-text-with-html-links
        $rexProtocol = '(https?://)';
        $rexDomain   = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
        $rexPort     = '(:[0-9]{1,5})?';
        $rexPath     = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
        $rexQuery    = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff\|]+?)?';
        $rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff\|]+?)?';

        return preg_replace_callback("&$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$|((</)[^aA])))&",
            function ($match) {
                return '<a href="' . $match[0] . '">'. $match[1] . $match[2] . $match[3] . $match[4] . $match[5] . '</a>';
            }, $text);
    }

    public static function terminateWith404() {
        header("HTTP/1.0 404 Not Found");
        echo '<html lang="en"><head><title>404 page not found</title></head><body><h1>Error 404</h1><p>Error 404: page not found</p></body></html>';
        exit;
    }

    public static function jqueryInclude() : string
    {
        return '<script src="/js/jquery-3.3.1.min.js"></script>';
    }

}
