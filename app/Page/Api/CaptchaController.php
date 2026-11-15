<?php

namespace Vici\Page\Api;

use Vici\Security\Altcha;

class CaptchaController
{
    public function getChallenge(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo (new Altcha($_ENV['ALTCHA_HMAC_KEY'] ?? ''))->createChallenge();
    }
}
