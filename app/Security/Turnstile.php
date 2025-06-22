<?php

namespace Vici\Security;

/**
 * Class voor het afhandelen van Cloudflare Turnstile CAPTCHA functionaliteit
 */
class Turnstile
{
    private string $secretKey;
    private string $siteKey;
    private string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Constructor
     * 
     * @param string $secretKey De Turnstile secret key
     * @param string $siteKey De Turnstile site key
     */
    public function __construct(string $secretKey, string $siteKey)
    {
        $this->secretKey = $secretKey;
        $this->siteKey = $siteKey;
    }

    /**
     * Valideer een Turnstile token
     * 
     * @param string $token Het token van de Turnstile widget
     * @param string $remoteIp Optioneel, het IP-adres van de gebruiker
     * @return bool True als validatie slaagt, anders false
     */
    public function validate(string $token, string $remoteIp = ''): bool
    {
        $verify = file_get_contents($this->verifyUrl, false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret'   => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]),
            ],
        ]));

        $response = json_decode($verify, true) ?: ['success' => false];
        return !empty($response['success']);
    }

    /**
     * Krijg de site key voor gebruik in templates
     * 
     * @return string De site key
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }
}
