<?php

namespace Vici\Session;

use Vici\Negotiator\LanguageNegotiator;
use Vici\I18n\Translator;
use Vici\DB\DBConnector;
use Vici\Model\User\User;
use Vici\Model\User\UserRepository;

const VICIBASE = 'https://vici.org';

class Session 
{
   
    const MAX_REQUESTS = 12;
    const RATE_LIMIT_SECONDS = 600;

    private $availableLanguages = [];
    private ?string $requestedAction = null;
    private ?string $requestedItem = null;
    private string $requestedVariant = 'default';
    private ?string $ip = null;
    private array $dbconnectors = [];
    private User $user;

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
        
        $this->loadUser();

        $urlParts = explode('/', $_SERVER['DOCUMENT_URI']);
        $this->requestedAction = $urlParts[1] ?? null;
        $this->requestedItem = $urlParts[2] ?? null;
        $this->requestedVariant = $urlParts[3] ?? $this->requestedVariant;
        $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    public function getDBConnector($database = 'MAIN') : DBConnector
    {
        if (!isset($this->dbconnectors[$database])) {
            $this->dbconnectors[$database] = new DBConnector($database);
        }
        return $this->dbconnectors[$database];
    }
    
    public function getViciBase() : string
    {
        if (isset($_ENV['VICIBASE'])) {
            return $_ENV['VICIBASE'];
        }
        return self::VICIBASE;
    }

    private function loadUser() : void
    {
        if (isset($_SESSION['user_id'])) {
            $userRepository = new UserRepository($this->getDBConnector());
            $this->user = $userRepository->findById($_SESSION['user_id']);
        }
    }

    public function getIP() : string
    {
        return $this->ip;
    }

    public function hasUser() : bool
    {
        return isset($this->user) && $this->user !== null;
    }   

    public function getUser() : User
    {
        return $this->user;
    }

    public function setUser(User $user) : void
    {
        $this->user = $user;
        $_SESSION['user_id'] = $user->getId();
    }

    public function clearUser() : void
    {
        unset($this->user);
        unset($_SESSION['user_id']);
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

    public function getRequestedItem() : int    
    {
        return (int)$this->requestedItem;
    }  

    public function getRequestedVariant() : string    
    {
        return $this->requestedVariant;
    }  

    public function setReturnURL($url) : void
    {
        $_SESSION['return'] = $url;
    }
    

}
