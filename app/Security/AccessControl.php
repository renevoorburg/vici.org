<?php

namespace Vici\Security;

use Dotenv\Dotenv;

class AccessControl
{

    public const RATE_LIMIT_SECONDS = 60;
    public const MAX_REQUESTS = 8;
    public const MAX_BOTLIKE_REQUESTS = 3;
    public const BLOCK_TIME_SECONDS = 900;

    private string $ip;
    private string $requested_action;
    private bool $is_logged_in;

    private bool $requires_token = false;
    private bool $requires_login = false;
    private bool $requires_authentication = false;

    private bool $is_trap_link = false;
    private bool $is_whitelist_link = false;
    private bool $is_rate_limited = false;
    private bool $is_possible_bot = false;


    public function __construct(string $ip, string $requested_action, bool $is_logged_in)
    {
        $this->ip = $ip;
        $this->requested_action = $requested_action;
        $this->is_logged_in = $is_logged_in;
    }

    public function setRequiresToken(bool $value): void
    {
        $this->requires_token = $value;
    }

    public function setRequiresLogin(bool $value): void
    {
        $this->requires_login = $value;
    }

    public function setRequiresAuthentication(bool $value): void
    {
        $this->requires_authentication = $value;
    }

    public function setIsTrapLink(bool $value): void
    {
        $this->is_trap_link = $value;
    }

    public function setIsWhitelistLink(bool $value): void
    {
        $this->is_whitelist_link = $value;
    }

    public function setIsRateLimited(bool $value): void
    {
        $this->is_rate_limited = $value;
    }

    public function setIsPossibleBot(bool $value): void
    {
        $this->is_possible_bot = $value;
    }

    public function enforceAndRun(callable $requested_action): void
    {
        if ($this->is_whitelist_link) {
            $this->unblockIP();
        }
            
        if ($this->hasBlockedIP()) {
            $this->denyAccess();
        }

        if ($this->requires_authentication || $this->requires_token || $this->requires_login) {
            if (!$this->is_logged_in && !$this->hasValidToken()) {
                self::enforceUserLogin();
            }
        }

        if ($this->is_trap_link) {
            $this->blockIP();
            self::denyAccess();
        }

        if ($this->is_rate_limited) {
            if (!$this->is_logged_in && !$this->hasValidToken()) {
                $key = "anon_ip_" . $this->requested_action . "_" . $this->ip;
                $hits = apcu_fetch($key) ?: 0;
                
                if ($hits > self::MAX_REQUESTS) {
                    self::denyAccessTemporarily();
                } else {
                    $hits++;
                    apcu_store($key, $hits, self::RATE_LIMIT_SECONDS);
                }
            }
        }

        if ($this->is_possible_bot) {
            if ($this->is_logged_in && !$this->hasValidToken()) {
                $key = "botlike_ip_" . $this->ip;
                $hits = apcu_fetch($key) ?: 0;
                
                if ($hits > self::MAX_BOTLIKE_REQUESTS) {
                    self::denyAccessTemporarily();
                } else {
                    $hits++;
                    apcu_store($key, $hits, self::BLOCK_TIME_SECONDS);
                }
            }
        }

        $requested_action();
    }


    private function isBot() : bool
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

    public function denyAccess(): void 
    {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    public static function enforceUserLogin() : void { 
        $uri = $_SERVER['REQUEST_URI'];

        if (self::isBot()) {
            self::denyAccess();
        } else {
            header('Location: /login?loginrequired&return=' . urlencode($uri));
            exit;
        }
    }

    public static function denyAccessTemporarily() : void
    {
        $uri = $_SERVER['REQUEST_URI'];
            
        if (self::isBot()) {
            header('HTTP/1.1 429 Too Many Requests');
            header("Retry-After: " . self::RATE_LIMIT_SECONDS);
            exit;
        } else {
            header('Location: /login?wait=' . self::RATE_LIMIT_SECONDS . '&return=' . urlencode($uri));
            exit;
        }
    }
}