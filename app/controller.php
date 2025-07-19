<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Vici\Session\Session;
use Vici\Page\Pages;
use Vici\API;
use Vici\Model\User\User;
use Vici\Security\AccessControl;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$session = new Session();

$accessControl = new AccessControl($session->getIP(), $session->getRequestedAction(), $session->hasUser());

switch ($session->getRequestedAction()) {
    case '':
        $action = fn() => (new Pages\HomePage($session))->display();
        break;
    case 'data-access':
        $accessControl->setIsTrapLink(true);
        $action = fn() => null;
        break;
    case 'favicon.ico':
        $accessControl->setIsWhitelistLink(true);
        $action = function () {
            $path = __DIR__ . '/assets/favicon.ico';
            header('Content-Type: image/x-icon');
            header('Content-Length: ' . filesize($path));
            readfile($path);
        };
        break;
    case 'vici':
        switch ($session->getRequestedVariant()) {
            case 'kml':
                $accessControl->setRequiresAuthentication(true);
                $action = fn() => (new API\KML($session))->get();
                break;
            default:
                $action = fn() => (new Pages\ItemPage($session))->display();
                break;
        }
        break;
    case 'geojson.php':
        $accessControl->setRequiresAuthentication(true);
        $action = fn() => (new API\GeoJSON($session))->get();
        break;
    case 'highlight.php':
        $accessControl->setRequiresToken(true);
        $action = fn() => (new API\Highlights($session))->get();
        break;
    case 'login':
        $action = fn() => (new Pages\LoginPage($session))->display();
        break;
    case 'logout':
        $session->clearUser();
        $action = fn() => (new Pages\HomePage($session))->display();
        break;
    case 'new':
        $action = fn() => (new Pages\HomePage($session))->display();
        break;
    case 'texts':
        echo $session->translator->getTranslationsJson(null, 'markerdef.');
        break;
    default:
        http_response_code(404);
        break;
}

$accessControl->enforceAndRun($action);
