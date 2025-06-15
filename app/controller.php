<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Vici\Session\Session;
use Vici\Page\Pages\HomePage;
use Vici\I18n\Translator;


$session = new Session();


switch ($session->getRequestedAction()) {
    case 'hello':
        echo "Hello World";
        echo "Language: " . $session->getLanguage();
        echo "Action: " . $session->getRequestedAction();
        break;
    case 'test':
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
