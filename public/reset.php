<?php
/**
Copyright 2013-6, René Voorburg, rene@digitopia.nl

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
To reset a forgotten password. Handles two steps, request & confirmation.
 */

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$db = new DBConnector(); // no errorhandling ... 


$error="";
$message="";
if (!empty($_GET['code'])) {
    // a confirmation code has been supplied, now look it up
    $getCode = $db->real_escape_string($_GET['code']);
    $sql = "SELECT reset_acc_id, acc_name FROM resetpw LEFT JOIN accounts ON reset_acc_id=acc_id WHERE conf_code='".$getCode."'";
    $result = $db->query($sql); // ... error handling ...
    
    if ($result->num_rows == 1 ) {
        // a valid confirmation code has been found
        list($reset_acc_id, $acc_name) = $result->fetch_row();
        $password="";
        
        if (  isset($_POST['password']) && (strlen($_POST['password'])>4)   ) {
            // we have a password too, so we are in the second phase
            
            // set password
            $password = trim($_POST['password']);
            $sql = "UPDATE accounts SET acc_passwd='".password_hash($password, PASSWORD_DEFAULT)."' WHERE acc_id='".$reset_acc_id."'";
            $result = $db->query($sql); // ... error handling ...
            
            // cleanup
            $sql = "DELETE FROM resetpw WHERE conf_code='".$getCode."'";
            $result = $db->query($sql); // ... error handling ...

            // update account level if needed
            $sql = "SELECT acc_level FROM accounts WHERE acc_id='".$reset_acc_id."'";
            $result = $db->query($sql);
            if ($result->fetch_object()->acc_level == 2) {
                $sql = "UPDATE accounts SET acc_level=3 WHERE acc_id='".$reset_acc_id."'";
                $result = $db->query($sql);

                $sql = "DELETE FROM unconfirmedaccs WHERE uconf_acc_id='".$reset_acc_id."'";
                $result = $db->query($sql); // ... error handling ...
            }

            
            // report
            $message = $lng->str('The password is now active.');
            
        } else {
            // we don't have a password yet (first phase) so display a entry form for the user
            $lng_username = $lng->str('Username');
            $lng_password = $lng->str('Password');
            $lng_passwordconfirm = $lng->str('Confirm password');
            $lng_reset = $lng->str('Reset');
            
            $message=<<<EOD
    <div style="margin-top:8px;width:500px">
    <form action="reset.php?code=$getCode" method="post" onsubmit="return validatePassword()">
    <div style="width:180px;float:left;line-height:3em">$lng_username:</div><span  style="line-height:3em">$acc_name</span><br/>
    <label  style="display:block;width:180px;float:left">$lng_password:</label><input style="width:200px" type="password" id="frm_password" name="password" value="$password"  /><br />
    <label  style="display:block;width:180px;float:left">$lng_passwordconfirm:</label><input style="width:200px" type="password" id="frm_passwordrepeat" name="passwordrepeat" value="$password" /><br />
    <input style="margin-left:180px;margin-top:16px" type="submit" value="$lng_reset">
    </div>
EOD;
            $scripts = "<script type='text/javascript'>\n";
            $scripts.= "lng_err_passwords_dont_match='".$lng->str('error:passwords_dont_match')."';\n";
            $scripts.= "lng_err_password_too_short='".$lng->str('error:password_too_short')."';\n";
            $scripts.= "lng_err_password_needs_number='".$lng->str('error:password_needs_number')."';\n";
            $scripts.= "lng_err_password_needs_uppercase='".$lng->str('error:password_needs_uppercase')."';\n";
            $scripts.= "lng_err_password_needs_lowercase='".$lng->str('error:password_needs_lowercase')."';\n";
            $scripts.= "lng_err_password_needs_special='".$lng->str('error:password_needs_special')."';\n";
            $scripts.= "</script>\n";
            $scripts.= '<script type="text/javascript" src="/js/common.js"></script>';
        }
    } else {
        $error = $lng->str("Confirmation code not found"). " - code: [$getCode]";
    }
} else {
    $error = $lng->str("No confirmation code supplied.");
}

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('scripts', $scripts);
$page->assign('content', "<p>$error $message</p>");
$page->assign('pagetitle', $lng->str("Reset password"));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');
