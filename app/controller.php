<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Vici\Session\Session;
use Vici\Page\Pages;
use Vici\API;
use Vici\Model\Users\User;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$session = new Session();

switch ($session->getRequestedAction()) {
    case '':
        $page = new Pages\HomePage($session);
        $page->display();
        break;
    case 'vici':
        $page = new Pages\ItemPage($session);
        $page->display();
        break;

    case 'geojson.php':
        $geojson = new API\GeoJSON($session);
        $geojson->get();
        break;
    case 'highlight.php':
        $highlights = new API\Highlights($session);
        $highlights->get();
        break;
    case 'login':
        $page = new Pages\LoginPage($session);
        $page->display();
        break;
    case 'logout':
        $session->clearUser();
        $page = new Pages\HomePage($session);
        $page->display();
        break;  
    case 'new':
        $page = new Pages\HomePage($session);
        $page->display();
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
