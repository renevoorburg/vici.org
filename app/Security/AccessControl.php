<?php

namespace Vici\Security;

use Dotenv\Dotenv;

class AccessControl
{

    public const RATE_LIMIT_SECONDS = 60;
    public const MAX_ANONYMOUS_REQUESTS = 8;
    public const MAX_BOTLIKE_REQUESTS = 3;
    public const BLOCK_TIME_SECONDS = 900;

    // escalating strikes: 15min → 24h → 7d → 30d
    private const STRIKE_TTLS = [900, 86400, 604800];
    private const MAX_BLOCK_TTL = 2592000;   // 30 days
    private const STRIKES_TTL = 7776000;     // strike count decays after 90 quiet days
    private const TRAP_WINDOW = 86400;       // trap hits counted over 24h

    private string $ip;
    private string $requested_action;
    private bool $has_authenticated_user;

    private bool $requires_token = false;
    private bool $requires_authenticated_user = false;
    private bool $requires_auth_or_token = false;


    private bool $is_bot_attracting_link = false;
    private bool $is_bot_trap_link = false;
    private bool $is_rate_limited_anonymously = false;



    public function __construct(string $ip, string $requested_action, bool $has_authenticated_user)
    {
        $this->ip = self::normalizeIp($ip);
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

    // dataservice endpoints: session login OR a valid partner token
    // (X-Vici-Token / CUST / UA / EXTSECRET). Interactive pages like
    // add/edit keep setRequiresAuthenticatedUser — tokens must not
    // grant edit rights.
    public function setRequiresAuthOrToken(bool $value): void
    {
        $this->requires_auth_or_token = $value;
    }

    public function setIsBotAttractingLink(bool $value): void
    {
        $this->is_bot_attracting_link = $value;
    }   

    public function setIsBotTrapLink(bool $value): void
    {
        $this->is_bot_trap_link = $value;
    }

    public function setIsRateLimitedAnonymously(bool $value): void
    {
        $this->is_rate_limited_anonymously = $value;
    }


    public function enforceAndRun(callable $requested_action): void
    {
        // Static assets (favicon) bypass IP bans so browsers still get them,
        // but they do NOT unban the IP. Source is public, so this is a
        // convenience, not a security boundary.
        if ($this->requested_action === 'favicon.ico') {
            $requested_action();
            return;
        }

        if ($this->hasBlockedIP()) {
            self::denyAccess('IP blocked');
        }

        if ($this->requires_token && !$this->hasValidToken()) {
            self::denyAccess('Invalid token');
        }

        if ($this->is_bot_trap_link) {
            $this->applyTrapHit();
            // keep /data-access/* out of search indexes — the visible link on
            // item pages is a honeypot, not real content
            header('X-Robots-Tag: noindex, nofollow');
            self::denyAccess('Blocked');
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
            !($this->has_authenticated_user || $this->hasValidToken()))
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

        if ($this->requires_auth_or_token &&
            !($this->has_authenticated_user || $this->hasValidToken()))
        {
            self::enforceUserAuthentication();
        }

        $requested_action();
    }

    private static function isAdvertisingBot() : bool
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

    /**
     * A single trap hit only earns a 403 — real users sometimes follow the
     * visible "data access" link. A strike (and thus an IP ban) is only
     * applied from the second trap hit within TRAP_WINDOW.
     */
    private function applyTrapHit(): void
    {
        $key = "trap_hits_{$this->ip}";
        $hits = apcu_inc($key, 1, $success, self::TRAP_WINDOW);
        if (!$success) {
            apcu_add($key, 0, self::TRAP_WINDOW);
            $hits = apcu_inc($key, 1, $success, self::TRAP_WINDOW);
            if (!$success) {
                $hits = ((int)apcu_fetch($key)) + 1;
                apcu_store($key, $hits, self::TRAP_WINDOW);
            }
        }
        if ($hits >= 2) {
            self::blockIp($this->ip);
        }
    }

    /**
     * Record a strike and (re)block the IP for an escalating duration:
     * 15min → 24h → 7d → 30d. Strikes decay after 90 quiet days.
     */
    public static function blockIp(string $ip): void
    {
        $ip = self::normalizeIp($ip);
        $strikesKey = "ip_strikes_{$ip}";

        // Increment the strike counter atomically. If the key does not yet
        // exist, seed it with 0 first and then increment.
        $strikes = apcu_inc($strikesKey, 1, $success, self::STRIKES_TTL);
        if (!$success) {
            apcu_add($strikesKey, 0, self::STRIKES_TTL);
            $strikes = apcu_inc($strikesKey, 1, $success, self::STRIKES_TTL);
            if (!$success) {
                $strikes = ((int)apcu_fetch($strikesKey)) + 1;
                apcu_store($strikesKey, $strikes, self::STRIKES_TTL);
            }
        }

        $ttl = self::STRIKE_TTLS[$strikes - 1] ?? self::MAX_BLOCK_TTL;
        apcu_store("ip_blocked_{$ip}", true, $ttl);
    }

    // IPv6 privacy addresses rotate within a /64 — key bans on the prefix
    private static function normalizeIp(string $ip): string
    {
        if (str_starts_with($ip, 'v4:') || str_starts_with($ip, 'v6:')) {
            return $ip; // already normalized
        }
        $packed = @inet_pton($ip);
        if ($packed !== false && strlen($packed) === 16) {
            return 'v6:' . substr(bin2hex($packed), 0, 16);
        }
        return 'v4:' . $ip;
    }

    private function hasValidToken(): bool
    {
        // an empty or missing token/env value must never count as a match
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $token = $headers['X-Vici-Token'] ?? $headers['x-vici-token']
            ?? $_SERVER['HTTP_X_VICI_TOKEN'] ?? '';
        $envToken = $_ENV['VICITOKEN'] ?? '';
        $token_match = $token !== '' && $envToken !== ''
            && hash_equals($envToken, $token);

        // Check extra tokens CUST1–CUST9
        if (!$token_match && $token !== '') {
            for ($i = 1; $i <= 9; $i++) {
                if (($env = $_ENV['CUST' . $i] ?? '') !== ''
                    && hash_equals($env, $token)) {
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

        // Check EXTSECRET in query string; it must match as a complete
        // parameter sequence, not as a substring inside another value
        $ext_secret_match = false;
        $ext_secret = $_ENV['EXTSECRET'] ?? null;
        if ($ext_secret && isset($_SERVER['QUERY_STRING'])) {
            $ext_secret_match = str_contains(
                '&' . $_SERVER['QUERY_STRING'] . '&',
                '&' . $ext_secret . '&'
            );
        }

        return $token_match || $useragent_match || $ext_secret_match;
    }

    public static function denyAccess($message = ''): void 
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