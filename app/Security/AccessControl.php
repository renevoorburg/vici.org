<?php

namespace Vici\Security;

use Dotenv\Dotenv;

class AccessControl
{

    public const RATE_LIMIT_SECONDS = 60;
    public const MAX_ANONYMOUS_REQUESTS = 8;
    public const MAX_BOTLIKE_REQUESTS = 3;
    public const BLOCK_TIME_SECONDS = 900;

    private string $ip;
    private string $requested_action;
    private bool $has_authenticated_user;

    private bool $requires_token = false;
    private bool $requires_authenticated_user = false;


    private bool $is_bot_attracting_link = false;
    private bool $is_bot_trap_link = false;
    private bool $is_whitelist_link = false;
    private bool $is_rate_limited_anonymously = false;



    public function __construct(string $ip, string $requested_action, bool $has_authenticated_user)
    {
        $this->ip = $ip;
        $this->requested_action = $requested_action;
        $this->has_authenticated_user = $has_authenticated_user;
    }

    public function setRequiresToken(bool $value): void
    {
        $this->requires_token = $value;
    }

    public function setRequiresAuthenticatedUser(bool $value): void
    {
        $this->requires_authenticated_user = $value;
    }

    public function setIsBotAttractingLink(bool $value): void
    {
        $this->is_bot_attracting_link = $value;
    }   

    public function setIsBotTrapLink(bool $value): void
    {
        $this->is_bot_trap_link = $value;
    }

    public function setIsWhitelistLink(bool $value): void
    {
        $this->is_whitelist_link = $value;
    }

    public function setIsRateLimitedAnonymously(bool $value): void
    {
        $this->is_rate_limited_anonymously = $value;
    }


    public function enforceAndRun(callable $requested_action): void
    {
        if ($this->is_whitelist_link) {
            $this->unblockIP();
        }
            
        if ($this->hasBlockedIP()) {
            $this->denyAccess('IP blocked');
        }

        if ($this->requires_token && !$this->hasValidToken()) {
            $this->denyAccess('Invalid token');
        }

        if ($this->is_bot_trap_link) {
            $this->blockIP();
            $this->denyAccess('Blocked');
        }

        if ($this->is_rate_limited_anonymously && !$this->has_authenticated_user) {
            $key = "anon_rate_limited_" . $this->requested_action . "_" . $this->ip;
            $hits = apcu_fetch($key) ?: 0;
            
            if ($hits > self::MAX_ANONYMOUS_REQUESTS) {
                self::denyAccessTemporarily($key);
            } else {
                $hits++;
                apcu_store($key, $hits, self::RATE_LIMIT_SECONDS);
            }
        }

        if ($this->is_bot_attracting_link && 
            !($this->has_authenticated_user || $this->has_valid_token))
        {
            $key = "botlike_rate_limited_" . $this->ip;
            $hits = apcu_fetch($key) ?: 0;
            
            if ($hits > self::MAX_BOTLIKE_REQUESTS) {
                self::denyAccessTemporarily($key);
            } else {
                $hits++;
                apcu_store($key, $hits, self::BLOCK_TIME_SECONDS);
            }
        }

        if ($this->requires_authenticated_user && !$this->has_authenticated_user) {
            self::enforceUserAuthentication();
        }

        $requested_action();
    }

    private function isAdvertisingBot() : bool
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
            return strpos($userAgent, 'bot') !== false || 
                strpos($userAgent, 'crawler') !== false || 
                strpos($userAgent, 'spider') !== false;
        }
        return false;
    }

    private function hasBlockedIP(): bool
    {
        return apcu_exists("ip_blocked_{$this->ip}");
    }

    private function blockIP(): void
    {
        apcu_store("ip_blocked_{$this->ip}", true, self::BLOCK_TIME_SECONDS);
    }

    private function unblockIP(): void
    {
        if (apcu_exists("ip_blocked_{$this->ip}")) {
            apcu_delete("ip_blocked_{$this->ip}");
        }
    }

    private function hasValidToken(): bool
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

    public function denyAccess($message = ''): void 
    {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied' . ($message ? ': ' . $message : '')]);
        exit;
    }
    
    public static function enforceUserAuthentication() : void { 
        $uri = $_SERVER['REQUEST_URI'];

        if (self::isAdvertisingBot()) {
            self::denyAccess();
        } else {
            header('Location: /login?loginrequired&return=' . urlencode($uri));
            exit;
        }
    }

    public static function denyAccessTemporarily(string $key) : void
    {
        $uri = $_SERVER['REQUEST_URI'];
            
        if (self::isAdvertisingBot()) {
            header('HTTP/1.1 429 Too Many Requests');
            header("Retry-After: " . self::RATE_LIMIT_SECONDS);
            exit;
        } else {
            // Lower the hit counter by 2 (atomic) to give humans some slack without resetting TTL
            if (function_exists('apcu_dec')) {
                @apcu_dec($key, 2, $success);
                if (isset($success) && !$success) {
                    // Key might not exist; nothing to do
                }
            } else {
                $hits = apcu_fetch($key) ?: 0;
                $hits = max(0, $hits - 2);
                apcu_store($key, $hits, self::RATE_LIMIT_SECONDS);
            }
            header('Location: /login?wait=' . self::RATE_LIMIT_SECONDS . '&return=' . urlencode($uri));
            exit;
        }
    }
}