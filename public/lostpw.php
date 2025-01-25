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
Helps users to recover a forgotten password.
 */

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$db = new DBConnector(); // no errorhandling ...

$html = '<p>'.$lng->str('lostpw_intro').'</p>';
$email = isset($_POST['frm_email']) ? trim((string)$_POST['frm_email']) : '';

if (!empty($email) ) {

    viciCommon::captchaCheck();

    // form was posted, so try to login
    $sql = "SELECT acc_id, acc_name, acc_realname, acc_level FROM accounts WHERE acc_email='".$db->real_escape_string($email)."'";
    $result = $db->query($sql); // error handling ...
    
    if ($result->num_rows==1){
        list($acc_id, $acc_name, $acc_realname, $acc_level) = $result->fetch_row();
        $uuid = ViciCommon::gen_uuid();
        
        $sql = "INSERT INTO resetpw VALUES (".$acc_id.", '".$uuid."', NULL) ON DUPLICATE KEY UPDATE conf_code='".$uuid."'";
        $result = $db->query($sql); // error handling ...
        
         // send confirmation message
        $message = sprintf($lng->str('reset password %s %s'), htmlentities($realname),
            viciCommon::getSiteBase() . "/reset.php?code=" . $uuid);
        $message = wordwrap($message, 70);

        $headers  = 'From: Vici <noreply@vici.org>' . "\r\n";
        $headers .= 'Reply-To: noreply@vici.org' . "\r\n";
        $headers .= 'Return-Path: noreply@vici.org' . "\r\n";
        $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";

        mail($email, $lng->str('Reset your password at Vici.org'), $message, $headers);
        
        $html = "<p>".$lng->str('An email with instructions has been sent.')."</p>\n";
    
    } else {
        $html = "<p>".$lng->str('Unknown email address.')."</p>\n";
    }
}


if (!isset($message)) {
    // still needed to display a form
    $lng_email = $lng->str('Email address');
    $lng_button = $lng->str('Submit');
    $lng_wrong_email = $lng->str('error:wrong_email');

    $captcha = viciCommon::captchaDisplay();
    $html.=<<<EOD
<div style="margin-top:8px;width:500px">
<form action="lostpw.php" method="post" onsubmit="return validateEmail()">
<label style="display:block;width:220px;float:left">$lng_email:</label><input style="width:200px" type="text" id="frm_email" name="frm_email"  value="$email" /><br />
$captcha
<input style="margin-left:220px;margin-top:8px" type="submit" value="$lng_button">
</div>
EOD;

}

$scripts=<<<EOD
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript">
lng_err_wrong_email="$lng_wrong_email"; 
</script>
EOD;

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('scripts', $scripts . viciCommon::captchaInclude());
$page->assign('content', $html);
$page->assign('pagetitle', $lng->str('Reset password'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');





