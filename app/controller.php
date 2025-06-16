<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Vici\Session\Session;
use Vici\Page\Pages\HomePage;
use Vici\API\GeoJSON;
use Vici\API\Highlights;
use Vici\Users\User;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$session = new Session();

switch ($session->getRequestedAction()) {
    case '':
        $page = new HomePage($session);
        $page->display();
        break;

    case 'geojson.php':
        $geojson = new GeoJSON($session);
        $geojson->get();
        break;
    case 'highlight.php':
        $highlights = new Highlights($session);
        $highlights->get();
        break;
    case 'login':
        $session->setUser(new User(1, 'John Doe', 'john@example.com'));
        $page = new HomePage($session);
        $page->display();
        break;
    case 'logout':
        $session->clearUser();
        $page = new HomePage($session);
        $page->display();
        break;  
    case 'new':
        $page = new HomePage($session);
        $page->display();
        break;
    case 'texts':
        echo $translator->getTranslationsJson(null, 'markerdef.');
        break;
    default:
        echo "Hello World";
        echo "Language: " . $session->getLanguage();
        echo "Action: " . $session->getRequestedAction();
        break;
}
