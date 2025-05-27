<?php 

/**
Page with form to login.
*/


require_once dirname(__FILE__).'/include/classLang.php';
require_once dirname(__FILE__).'/include/classSession.php';
require_once dirname(__FILE__).'/include/classViciCommon.php';
require_once dirname(__FILE__).'/include/classDBConnector.php';
require_once dirname(__FILE__).'/include/classPage.php';

$lng = new Lang();
$session = new Session($lng->getLang());
// Bepaal return URL: uit ?return, of val terug op /
$returnUrl = $_GET['return'] ?? ($_SESSION['return'] ?? '/');
$_SESSION['return'] = ($returnUrl === '/login.php') ? '/' : $returnUrl;
$db = new DBConnector(); 

$errorMsg = '';
$name = isset($_POST['name']) ? (string)$_POST['name'] : null;
$password = isset($_POST['password']) ? trim($_POST['password']) : null;

if (!empty($name) || !empty($password)) {

    viciCommon::captchaCheck();

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

            $return = $_SESSION['return'] ?? '/';
            unset($_SESSION['return']);
            header("Location: $return");
            exit;
        
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

$captcha = viciCommon::captchaDisplay();

$msg = "";
if (isset($_GET['wait'])) {
    $sec = (int)$_GET['wait'];
    $msg = "<p>".sprintf($lng->str("error: Page limit for anonymous users reached. Log in or wait %s seconds."), $sec)."</p>";
}
if (isset($_GET['loginrequired'])) {
    $msg = "<p>" . sprintf($lng->str("error: You need to log in to access this dataservice."), $sec) . "</p>";
}

$form = $msg;

$form .= <<<EOD
<div style="margin-top:8px; width:500px">
<form action="login.php" method="post">
<label style="display:block; width:160px; float:left">$lng_username:</label><input style="width:200px" type="text" id="frm_name" name="name"  value="$name" /><br><br>
<label  style="display:block; width:160px; float:left">$lng_password:</label><input style="width:200px" type="password" id="frm_password" name="password" /><br>
$captcha<br />
<input style="margin-left:160px; margin-top:8px;" type="submit" value="$lng_login">
</div>
<p>$lng_register</p>
EOD;

// display page:
$page = new Page();

$page->assign('lang', $lng->getLang());
$page->assign('scripts', '<script type="text/javascript" src="/js/common.js"></script>' . viciCommon::captchaInclude() );
$page->assign('content', "<p>".$errorMsg."</p>".$form);
$page->assign('pagetitle', $lng_login);
$page->assign('session', ViciCommon::sessionBox($lng, $session));
$page->assign('leftnav', ViciCommon::mainMenu($lng));

if (isset($_GET['return'])) {
    $session->setReturnURL($_GET['return']);
}

$page->display('content.tpl');
