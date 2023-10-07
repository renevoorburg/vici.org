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
Page with form to login.
*/


require_once dirname(__FILE__).'/include/classLang.php';
require_once dirname(__FILE__).'/include/classSession.php';
require_once dirname(__FILE__).'/include/classViciCommon.php';
require_once dirname(__FILE__).'/include/classDBConnector.php';
require_once dirname(__FILE__).'/include/classPage.php';

if (! isset($_SERVER['HTTPS']) && ! ViciCommon::isTesting()) {
    header("Location: https://vici.org/login.php", true, 301);
    exit();
}

$lng = new Lang();
$session = new Session($lng->getLang());
$db = new DBConnector(); // no errorhandling 

$errorMsg = '';
$name = isset($_POST['name']) ? (string)$_POST['name'] : null;
$password = isset($_POST['password']) ? trim($_POST['password']) : null;

if (!empty($name) || !empty($password)) {


    $response = $_POST["g-recaptcha-response"];
    $secret = "6LeVUQ4UAAAAAAsbcmAmT4a-yhOlCkMViqSH4KQk";
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}&remoteip={$_SERVER['REMOTE_ADDR']}");
    $captcha_success = json_decode($verify);

    if ($captcha_success->success==false) {
        echo "<p>You are a bot! Go away!</p>";
        exit;
    }



    // form was posted, so try to login
    $sql = "SELECT acc_id, acc_name, acc_realname, acc_level, acc_passwd, acc_email FROM accounts WHERE acc_name='".$db->real_escape_string($name)."'";
    $result = $db->query($sql); // no errorhandling
    
    if ($result->num_rows==1) {
        $rowObj =  $result->fetch_object();

        if (password_verify($password, $rowObj->acc_passwd)) {
            $_SESSION['acc_id'] = $rowObj->acc_id;
            $_SESSION['acc_name'] = $name;
            $_SESSION['acc_realname'] = $rowObj->acc_realname;
            $_SESSION['acc_level'] = $rowObj->acc_level;
            $_SESSION['acc_email'] = $rowObj->acc_email;

            $return =  $_SESSION['return'];
            unset($_SESSION['return']);
            if (empty($return) || ($return=="/login.php")) {
                $return = "/";
            }

            header("Location: $return");
        
            /* acc_level: b440a58cca0ddde37a9289ae1d766b3f40f3e92f
            0: deleted: record kept for integrity, acc. data flushed
            1: locked: can do nothing until situation cleared
            2: not confirmed: new account, waits for confirmation
            3: confirmed, not trusted (postings need to be checked)
            4: trusted
            5: admin
            */
        } else {
            // try previous approach:
            $pw = (isset($password) ? sha1($password) : null);
            $sql = "SELECT acc_id, acc_name, acc_realname, acc_level, acc_email FROM accounts WHERE acc_name='" . $db->real_escape_string($name) . "' AND acc_pw='" . $db->real_escape_string($pw) . "'";
            $result = $db->query($sql); // no errorhandling

            if ($result->num_rows == 1) {

                //
                list($_SESSION['acc_id'], $_SESSION['acc_name'], $_SESSION['acc_realname'], $_SESSION['acc_level'], $_SESSION['acc_email']) = $result->fetch_row();
                $return = $_SESSION['return'];
                unset($_SESSION['return']);

                if (empty($return) || ($return == "/login.php")) {
                    $return = "/";
                }

                // now save salted pw:
                $sql = "UPDATE accounts SET acc_passwd = '" . password_hash($password, PASSWORD_DEFAULT) . "', acc_pw = '' WHERE acc_id=" . $_SESSION['acc_id'] . "";
                $db->query($sql);

                header("Location: $return");
            } else {
                $errorMsg = $lng->str("Unknown username or password.");
           }
           $errorMsg = $lng->str("Incorrect password.");
       }
    } else {
        $errorMsg = $lng->str("Unknown user.");
    }
}


$lng_username = $lng->str('Username');
$lng_password = $lng->str('Password');
$lng_login    = $lng->str('Login');
$lng_register = $lng->str("Don't have an account? Create one.");

$form=<<<EOD
<div style="margin-top:8px; width:500px">
<form action="login.php" method="post">
<label style="display:block; width:160px; float:left">$lng_username:</label><input style="width:200px" type="text" id="frm_name" name="name"  value="$name" /><br />
<label  style="display:block; width:160px; float:left">$lng_password:</label><input style="width:200px" type="password" id="frm_password" name="password" /><br />
<div style="margin-left:160px;margin-top:16px;" class="g-recaptcha" data-sitekey="6LeVUQ4UAAAAAKjv7--1O-LnU6Cp-g1fBJw4ItMv"></div><br />
<input style="margin-left:160px; margin-top:8px;" type="submit" value="$lng_login">
</div>
<p>$lng_register</p>
EOD;

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('scripts', '<script type="text/javascript" src="/js/common.js"></script><script src="https://www.google.com/recaptcha/api.js" async defer></script>');
$page->assign('content', "<p>".$errorMsg."</p>".$form);
$page->assign('pagetitle', $lng_login);
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

if (isset($_GET['return'])) {
    $session->setReturnURL($_GET['return']);
}

$page->display('content.tpl');

