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

    public function enforceAnonymousRateLimit($max = 12, $seconds = 600)
    {
        if ($this->hasUser()) {
            return; // no limit
        }
    
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "anon_ip_" . $ip;
    
        $hits = apcu_fetch($key) ?: 0;
        $hits++;
        apcu_store($key, $hits, $seconds);
    
        if ($hits > $max) {
            $uri = $_SERVER['REQUEST_URI'];
            header("Retry-After: $seconds"); 
            header('Location: /login.php?wait=' . $seconds . '&return=' . urlencode($uri));
            exit;
        }
    }


}
