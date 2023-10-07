<?php

class Session 
{
    private $lang = '';
    

    public function __construct($lang)
    {
        session_start();
        date_default_timezone_set('Europe/Rome');
        $this->lang = $lang;
    }
    
    public function getUserId()
    {
        if (isset($_SESSION['acc_id'])) {
            return $_SESSION['acc_id'];
        } else {
            return 0;
        }
    }

    public function getAccountName()
    {
        return $_SESSION['acc_name'];
    }

    public function getAccountEmail()
    {
        return $_SESSION['acc_email'];
    }


    public function hasUser() {
        if ($this->getUserId()) {
            return true;
        } else {
            return false;
        }
    }

    public function setReturnURL($url)
    {
        $_SESSION['return'] = $url;
    }


}
