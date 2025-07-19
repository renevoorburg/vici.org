<?php

namespace Vici\Security;

use Dotenv\Dotenv;

class AccessControl
{

    public const RATE_LIMIT_SECONDS = 60;
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

        // if ($this->is_trap_link) {
        //     if (!$this->isCaptchaPassed()) {
        //         header("Location: /captcha-check");
        //         exit;
        //     } else {
        //         apcu_store("ip_blocked_$ip", true, 900); 
        //         http_response_code(403);
        //         echo "Je bent tijdelijk geblokkeerd vanwege verdacht gedrag.";
        //         exit;
        //     }
        // }

        // if ($this->isBlocked()) {
        //     http_response_code(403);
        //     echo "Je bent tijdelijk geblokkeerd vanwege verdacht gedrag.";
        //     exit;
        // }

        // if (!$this->is_logged_in && $this->requires_login) {
        //     if ($this->tooManyLoginPromptsWithoutLogin()) {
        //         header("Location: /captcha-check");
        //         exit;
        //     }
        //     $this->logLoginPrompt();
        //     header("Location: /login");
        //     exit;
        // }

        // if (!$this->is_logged_in && $this->anonymous_access_suspicious && $this->isAnonymousAccessSuspicious()) {
        //     header("Location: /captcha-check");
        //     exit;
        // }

        // if ($this->requires_token && !$this->hasValidToken()) {
        //     http_response_code(403);
        //     echo "Geen geldige toegangstoken.";
        //     exit;
        // }

        // Alles oké, voer de actie uit
        $requested_action();
    }

    private function isBlocked(): bool
    {
        return apcu_exists("ip_blocked_{$this->ip}");
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

    private function isSuspiciousAnonymousUsage(): bool
    {
        $key = "fragile_hit_{$this->requested_action}_{$this->ip}";
        $hits = apcu_fetch($key) ?: [];
        $now = time();
        $hits = array_filter($hits, fn($ts) => $ts > $now - 600);
        $hits[] = $now;
        apcu_store($key, $hits, 660);

        return count($hits) > 2;
    }

    private function tooManyLoginPromptsWithoutLogin(): bool
    {
        $key = "login_prompts_{$this->ip}";
        $hits = apcu_fetch($key) ?: [];
        $now = time();
        $hits = array_filter($hits, fn($ts) => $ts > $now - 900);
        $hits[] = $now;
        apcu_store($key, $hits, 930);

        return count($hits) > 2;
    }

    private function logLoginPrompt(): void
    {
        $key = "login_prompts_{$this->ip}";
        $hits = apcu_fetch($key) ?: [];
        $now = time();
        $hits[] = $now;
        apcu_store($key, $hits, 930);
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