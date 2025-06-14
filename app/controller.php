<?php

require_once __DIR__ . '/../vendor/autoload.php';

$urlParts = explode('/', $_SERVER['DOCUMENT_URI']);
$action = $urlParts[1];

$languageNegotiator = new \Vici\Negotiator\LanguageNegotiator(['en', 'de', 'fr', 'nl']);
$language = $languageNegotiator->negotiate();


switch ($action) {
    case '':
        echo "home";
        break;
    case 'hello':
        echo "Hello World";
        echo "Language: " . $language;
        echo "Action: " . $action;
        break;
    default:
        echo "Hello World";
        break;
}
