<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Vici\Session\Session;
use Vici\Page\Pages;
use Vici\API;
use Vici\Model\Users\User;
use Vici\Security\AccessControl;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$session = new Session();

$accessControl = new AccessControl($session->getIP(), $session->getRequestedAction(), $session->hasUser());

switch ($session->getRequestedAction()) {
    case '':
        $action = fn() => (new Pages\HomePage($session))->display();
        break;
    case 'vici':
        $action = fn() => (new Pages\ItemPage($session))->display();
        break;
    case 'geojson.php':
        $accessControl->setRequiresToken(true);
        $action = fn() => (new API\GeoJSON($session))->get();
        break;
    case 'highlight.php':
        $accessControl->setRequiresToken(false);
        $action = fn() => (new API\Highlights($session))->get();
        break;
    case 'login':
        $action = fn() => (new Pages\LoginPage($session))->display();
        break;
    case 'logout':
        $session->clearUser();
        $action = fn() => (new Pages\HomePage($session))->display();
        break;
    case 'data-access':
        $accessControl->setIsTrapLink(true);
        $action = fn() => (new Pages\HomePage($session))->display();
        break;  
    case 'new':
        $action = fn() => (new Pages\HomePage($session))->display();
        break;
    case 'texts':
        echo $session->translator->getTranslationsJson(null, 'markerdef.');
        break;
    default:
        echo "Hello World";
        echo "Language: " . $session->getLanguage();
        echo "Action: " . $session->getRequestedAction();
        break;
}

$accessControl->enforceAndRun($action);
