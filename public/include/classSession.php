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
        
        if ($hits > $max) {
            $uri = $_SERVER['REQUEST_URI'];
            
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
                if (strpos($userAgent, 'bot') !== false || 
                    strpos($userAgent, 'crawler') !== false || 
                    strpos($userAgent, 'spider') !== false) {
                    header('HTTP/1.1 429 Too Many Requests');
                    header("Retry-After: $seconds");
                    exit;
                }
            }
            
            header('Location: /login.php?wait=' . $seconds . '&return=' . urlencode($uri));
            exit;
        } else {
            $hits++;
            apcu_store($key, $hits, $seconds);
        }
    }


}
