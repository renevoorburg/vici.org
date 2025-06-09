<?php

namespace Vici;

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class PublicApiAuthenticator
{
    public static function isAuthorized(): bool
    {
        $headers = getallheaders();
        $token = $headers['X-Vici-Token'] ?? '';
        $token_match = $token === ($_ENV['VICITOKEN'] ?? '');

        // Check extra tokens CUST1–CUST9
        if (!$token_match && $token !== '') {
            for ($i = 1; $i <= 9; $i++) {
                if (($env = $_ENV['CUST' . $i] ?? '') !== '' && $token === $env) {
                    $token_match = true;
                    break;
                }
            }
        }

        // Check user-agent prefix UA1–UA9
        $useragent_match = false;
        if (!$token_match && isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
            for ($i = 1; $i <= 9; $i++) {
                $prefix = $_ENV['UA' . $i] ?? '';
                if ($prefix !== '' && str_starts_with($ua, $prefix)) {
                    $useragent_match = true;
                    break;
                }
            }
        }

        // Check EXTSECRET in query string
        $ext_secret_match = false;
        $ext_secret = $_ENV['EXTSECRET'] ?? null;
        if ($ext_secret && isset($_SERVER['QUERY_STRING'])) {
            $ext_secret_match = str_contains($_SERVER['QUERY_STRING'], $ext_secret);
        }

        return $token_match || $useragent_match || $ext_secret_match;
    }

    public static function enforceAuthorization(): void
    {
        if (!self::isAuthorized()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
    }

    
}