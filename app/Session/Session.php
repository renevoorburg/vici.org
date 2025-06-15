<?php

namespace Vici\Session;

use Vici\Negotiator\LanguageNegotiator;
use Vici\I18n\Translator;

class Session 
{
   
    const MAX_REQUESTS = 12;
    const RATE_LIMIT_SECONDS = 600;

    private $availableLanguages = [];
    private $requestedAction = '';
    public Translator $translator;
    
    public function __construct()
    {
        session_start();
        date_default_timezone_set('Europe/Rome');
        
        $this->availableLanguages = Translator::getAvailableLanguages();
        
        if (LanguageNegotiator::hasForcedLanguage() || !$this->hasSessionLanguage()) {
            $languageNegotiator = new LanguageNegotiator($this->availableLanguages);
            $this->setLanguage($languageNegotiator->negotiate());
        } 
        $this->translator = new Translator($this->getLanguage());
        
        $urlParts = explode('/', $_SERVER['DOCUMENT_URI']);
        $this->requestedAction = $urlParts[1];

    }

    
    public function getUserId() : int
    {
        return isset($_SESSION['acc_id']) ? $_SESSION['acc_id'] : 0;
    }

    public function getAccountName() : string
    {
        return $_SESSION['acc_name'] ?? '';
    }

    public function getAccountEmail() : string
    {
        return $_SESSION['acc_email'] ?? '';
    }

    public function hasUser() : bool
    {
        return (bool)$this->getUserId();
    }
    
    public function getAvailableLanguages() : array
    {
        return $this->availableLanguages;
    }
    
    public function getLanguage() : string
    {
        return $_SESSION['lang'];
    }

    public function setLanguage(string $language) : void
    {
        $_SESSION['lang'] = $language;
    }
    
    private function hasSessionLanguage() : bool
    {
        return isset($_SESSION['lang']);
    }

    public function getRequestedAction() : string
    {
        return $this->requestedAction;
    }  

    public function setReturnURL($url) : void
    {
        $_SESSION['return'] = $url;
    }

    public function enforceAnonymousRateLimit() : void
    {
        if ($this->hasUser()) {
            return; // no limit
        }
    
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "anon_ip_" . $ip;
        $hits = apcu_fetch($key) ?: 0;
        
        if ($hits > self::MAX_REQUESTS) {
            self::denyAccessTemporarily();
        } else {
            $hits++;
            apcu_store($key, $hits, self::RATE_LIMIT_SECONDS);
        }
    }

    public static function isBot() : bool
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
            return strpos($userAgent, 'bot') !== false || 
                strpos($userAgent, 'crawler') !== false || 
                strpos($userAgent, 'spider') !== false;
        }
        return false;
    }

    public static function denyAccess() : void { 
        $uri = $_SERVER['REQUEST_URI'];

        if (self::isBot()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied']);
            exit;
        } else {
            header('Location: /login.php?loginrequired&return=' . urlencode($uri));
            exit;
        }
    }

    public static function denyAccessTemporarily() : void
    {
        $uri = $_SERVER['REQUEST_URI'];
            
        if (self::isBot()) {
            header('HTTP/1.1 429 Too Many Requests');
            header("Retry-After: " . self::RATE_LIMIT_SECONDS);
            exit;
        } else {
            header('Location: /login.php?wait=' . self::RATE_LIMIT_SECONDS . '&return=' . urlencode($uri));
            exit;
        }
    }
    

}
