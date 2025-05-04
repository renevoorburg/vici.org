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
Pages that allows users to register to create an account at Vici.org.
 */

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$db = new DBConnector(); // no errorhandling ...

$errorMsg="";

if ( isset( $_POST ) ) {
    $postArr = &$_POST ;
} else {
    $postArr = &$HTTP_POST_VARS ;
}
$name = isset($postArr["name"]) ? trim($postArr["name"]) : '';
$password = isset($postArr["password"]) ? trim($postArr["password"]) : '';
$passwordrepeat = isset($postArr["passwordrepeat"]) ? trim($postArr["passwordrepeat"]) : '';
$realname = isset($postArr["realname"]) ? trim($postArr["realname"]) : '';
$email = isset($postArr["email"]) ? trim($postArr["email"]) : '';

if ( (strlen($name)>3) && (strlen($password)>5) && ($passwordrepeat==$password) && (strlen($realname)>3) && strlen($email)>4  ) {

    viciCommon::captchaCheck();

    //we have all required vars
    $nameSafe = $db->real_escape_string($name);
    $emailSafe = $db->real_escape_string($email);

    // test for unique username
    $sql =  "SELECT acc_name FROM accounts WHERE acc_name='".$nameSafe."'";
    $result = $db->query($sql); // error handling...
    if ($result->num_rows > 0) { $errorMsg = $lng->str('Username taken.'); }
    
    if (empty($errorMsg)) {
        // test for unique emailaddress
        $sql =  "SELECT acc_name FROM accounts WHERE acc_email='".$emailSafe."'";
        $result = $db->query($sql); // error handling...
        if ($result->num_rows > 0) { $errorMsg = $lng->str('Email address taken.'); }
    }
    
    if (empty($errorMsg)) {
        // we're fine, create account

        // insert account data
        $sql = "INSERT INTO accounts VALUES (NULL, '" . $nameSafe . "', '', '" . $db->real_escape_string($realname) . "', '" . $emailSafe . "', 2, '".password_hash($password, PASSWORD_DEFAULT)."')";
        $result = $db->query($sql); // error handling...

        // insert confirmation data
        $id = $db->insert_id;
        $uuid = ViciCommon::gen_uuid();
        $sql = "INSERT INTO unconfirmedaccs VALUES (" . $id . ", '" . $uuid . "', NULL)";
        $result = $db->query($sql); // error handling...

        // send confirmation message
        $message = sprintf($lng->str('confirmation mail %s %s'), htmlentities($realname),
            viciCommon::getSiteBase() . "/confirm.php?code=" .$uuid);
        $message = wordwrap($message, 70);

        $headers  = 'From: Vici <noreply@vici.org>' . "\r\n";
        $headers .= 'Reply-To: noreply@vici.org' . "\r\n";
        $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";


        mail($email, $lng->str('Activate your account at Vici.org'), $message, $headers, '-f noreply@vici.org');

        // report
        $content = "<p>" . sprintf($lng->str('Account created, activation required %s'), $email) . "</p>";
    }
} else {
    // invalid or no input
    if ( !empty($name) && !empty($password) && !empty($realname) && !empty($realname) && !empty($email) ) {
        // there was some input, we can only get here when javascript is off
        $errorMsg = $lng->str('Insufficient input. Did you supply all requested fields? Did both passwords match?');
    }
}

// what should be here? now at least it is defined..:
$lng_createacc = $lng->str("Create account");

if (empty($message)) {
    // form to be shown (first time or entry was not valid, so show again):

    $lng_username = $lng->str('Username');
    $lng_password = $lng->str('Password');
    $lng_passwordconfirm = $lng->str('Confirm password');
    $lng_name = $lng->str("Name");
    $lng_email = $lng->str('Email address');
    $lng_createacc = $lng->str("Create account");

    $captcha = viciCommon::captchaDisplay();

    $content=<<<EOD
<div style="margin-top:8px; width:500px">
<form action="register.php" method="post" onsubmit="return validateRegistration()">
<label style="display:block;width:180px;float:left">$lng_username:</label><input style="width:200px" type="text" id="frm_name" name="name" value="$name" /><br />
<label style="display:block;width:180px;float:left">$lng_password:</label><input style="width:200px" type="password" id="frm_password" name="password" value="$password"  /><br />
<label style="display:block;width:180px;float:left">$lng_passwordconfirm:</label><input style="width:200px" type="password" id="frm_passwordrepeat" name="passwordrepeat" value="$passwordrepeat" /><br />

<div style="margin-top:24px"><label  style="display:block;width:180px;float:left">$lng_name:</label><input style="width:200px" type="text" id="frm_realname" name="realname" value="$realname" /><br />
<label  style="display:block; width:180px; float:left">$lng_email:</label><input style="width:200px" type="text" id="frm_email" name="email" value="$email" /></div>
$captcha
<input style="margin-left:180px;margin-top:16px" type="submit" value="$lng_createacc">
</div>
EOD;

    $content = "<p>$errorMsg</p>".$content;
}

$scripts = "<script type='text/javascript'>\n";
$scripts.= "lng_err_username_too_short='".$lng->str('error:username_too_short')."';\n";
$scripts.= "lng_err_passwords_dont_match='".$lng->str('error:passwords_dont_match')."';\n";
$scripts.= "lng_err_password_too_short='".$lng->str('error:password_too_short')."';\n";
$scripts.= "lng_err_password_needs_number='".$lng->str('error:password_needs_number')."';\n";
$scripts.= "lng_err_password_needs_uppercase='".$lng->str('error:password_needs_uppercase')."';\n";
$scripts.= "lng_err_password_needs_lowercase='".$lng->str('error:password_needs_lowercase')."';\n";
$scripts.= "lng_err_password_needs_special='".$lng->str('error:password_needs_special')."';\n";
$scripts.= "lng_err_fullname_too_short='".$lng->str('error:fullname_too_short')."';\n";
$scripts.= "lng_err_wrong_email='".$lng->str('error:wrong_email')."';\n";
$scripts.= "</script>\n";
$scripts.= '<script type="text/javascript" src="/js/common.js"></script>'."\n";
$scripts.= viciCommon::captchaInclude();

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('scripts', $scripts);
$page->assign('content', $content);
$page->assign('pagetitle', $lng_createacc);
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');
