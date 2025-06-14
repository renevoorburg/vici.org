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
Landing page for confirmation of user accounts (link to here sent by mail).
*/

require_once (dirname(__FILE__).'/include/classLang.php');
require_once (dirname(__FILE__).'/include/classSession.php');
require_once (dirname(__FILE__).'/include/classViciCommon.php');
require_once (dirname(__FILE__).'/include/classDBConnector.php');
require_once (dirname(__FILE__).'/include/classPage.php');

$lng = new Lang();
$session = new Session($lng->getLang());

$db = new DBConnector(); // no errorhandling ... 

$error = '';
$message = '';
if (!empty($_GET['code'])) {
    // a confirmation code has been supplied, now look it up
    $getCode = htmlentities($_GET['code']);
    $getCodeSafe = $db->real_escape_string($getCode);
        
    $sql = "SELECT uconf_acc_id, conf_code FROM unconfirmedaccs WHERE conf_code='".$getCodeSafe."'";
    $result = $db->query($sql);  // ... error handling ...
    
    if ($result->num_rows == 1 ) {       
        // the confirmation code has been found
        list($uconf_acc_id) = $result->fetch_row();
        
        $sql =  "SELECT acc_id, acc_name, acc_realname, acc_level FROM accounts WHERE acc_id=".$uconf_acc_id." AND acc_level > 2";
        $result = $db->query($sql); // ... error handling ...
        
        if ($result->num_rows < 1 ) {
            // activate account
            $sql = "UPDATE accounts SET acc_level=3 WHERE acc_id=$uconf_acc_id";
            $result = $db->query($sql);  // ... error handling ...
        
            // delete confirmation code
            $sql = "DELETE FROM unconfirmedaccs WHERE conf_code='".$getCodeSafe."'";
            $result = $db->query($sql); // ... error handling ...
        
            $message = $lng->str("Your account has been activated.")."<br />".$lng->str("Login to continue.");    
        } else {
            $error = $lng->str("Account already activated.");
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
$page->assign('content', "<p>$error $message</p>");
$page->assign('pagetitle', $lng->str('Account activation'));
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

$page->display('content.tpl');

?>