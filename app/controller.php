<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Vici\Session\Session;
use Vici\Page\Pages\HomePage;
use Vici\I18n\Translator;


$session = new Session();

$translator = new Translator($session->getLanguage());



switch ($session->getRequestedAction()) {
    case 'hello':
        echo "Hello World";
        echo "Language: " . $session->getLanguage();
        echo "Action: " . $session->getRequestedAction();
        break;
    case '':
        $page = new HomePage();
        $page->display();
        break;
    default:
        echo "Hello World";
        echo "Language: " . $session->getLanguage();
        echo "Action: " . $session->getRequestedAction();
        break;
}
