<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Vici\Session\Session;
use Vici\Page\Pages;
use Vici\API;
use Vici\Page\Api as ApiPage;
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
                $accessControl->setRequiresAuthenticatedUser(true);
                $action = fn() => (new API\KML($session))->get();
                break;
            default:
                $accessControl->setIsRateLimitedAnonymously(true);
                $action = fn() => (new Pages\ItemPage($session))->display();
                break;
        }
        break;
    case 'api':
        switch ($session->getRequestedItem()) {
            case 'users':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $accessControl->setIsBotAttractingLink(true);
                    $accessControl->setIsRateLimitedAnonymously(true);
                    $action = fn() => (new ApiPage\UserApiController($session))->postCreateUser();
                } else {
                    http_response_code(405);
                    $action = fn() => null;
                }
                break;
            default:
                http_response_code(404);
                $action = fn() => null;
                break;
        }
        break;
    case 'image':
        $accessControl->setIsRateLimitedAnonymously(true);
        $action = fn() => (new Pages\ImagePage($session))->display();
        break;
    case 'search':
        $accessControl->setIsRateLimitedAnonymously(true);
        $action = fn() => (new Pages\SearchPage($session))->display();
        break;
    case 'additions':
        $accessControl->setIsRateLimitedAnonymously(true);
        $action = fn() => (new Pages\RecentlyAddedPage($session))->display();
        break;
    case 'changes':
        $accessControl->setIsRateLimitedAnonymously(true);
        $action = fn() => (new Pages\RecentlyChangedPage($session))->display();
        break;
    case 'geojson.php':
        $accessControl->setRequiresToken(true);
        $action = fn() => (new API\GeoJSON($session))->get();
        break;
    case 'highlight.php':
        $accessControl->setRequiresToken(true);
        $action = fn() => (new API\Highlights($session))->get();
        break;
    case 'login':
        $accessControl->setIsBotAttractingLink(true);
        $action = fn() => (new Pages\LoginPage($session))->display();
        break;
    case 'register':
        $accessControl->setIsBotAttractingLink (true);
        $action = fn() => (new Pages\RegisterPage($session))->display();
        break;
    case 'logout':
        $session->clearUser();
        $action = fn() => (new Pages\HomePage($session))->display();
        break;
    default:
        http_response_code(404);
        break;
}

$accessControl->enforceAndRun($action);
