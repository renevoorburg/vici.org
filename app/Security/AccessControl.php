<?php

namespace Vici\Security;

use Dotenv\Dotenv;
use Vici\DB\DBConnector;

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
    private const SUBNET_BLOCK_THRESHOLD = 20; // trap hits per subnet within TRAP_WINDOW
    private const SUBNET_BLOCK_TTL = 86400;    // subnet block duration (24h)

    private string $ip;
    private string $requested_action;
    private bool $has_authenticated_user;
    private DBConnector $db;

    private bool $requires_token = false;
    private bool $requires_authenticated_user = false;
    private bool $requires_auth_or_token = false;


    private bool $is_bot_attracting_link = false;
    private bool $is_bot_trap_link = false;
    private bool $is_rate_limited_anonymously = false;



    public function __construct(string $ip, string $requested_action, bool $has_authenticated_user, DBConnector $db)
    {
        $this->ip = self::normalizeIp($ip);
        $this->requested_action = $requested_action;
        $this->has_authenticated_user = $has_authenticated_user;
        $this->db = $db;
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

        if ($this->isBlocked()) {
            self::denyAccess('IP blocked');
        }

        if ($this->requires_token && !$this->hasValidToken()) {
            self::denyAccess('Invalid token');
        }

        if ($this->is_bot_trap_link) {
            if ($this->applyTrapHit()) {
                self::denyAccess('Blocked');
            }
            // first hit: send the visitor home instead of a bare 403 — real
            // users sometimes follow old indexed links. The hit is still
            // counted; a repeat within TRAP_WINDOW earns a strike. The 301
            // also tells search engines the trap URL moved to /.
            header('X-Robots-Tag: noindex, nofollow');
            header('Location: /', true, 301);
            exit;
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

    // Blocks live in the shared access_control table — APCu is per
    // php-fpm worker, so a block there would only cover ~1/N requests
    private function isBlocked(): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT ac_key FROM access_control " .
                "WHERE ac_key IN (?, ?) AND ac_expires > UNIX_TIMESTAMP()"
            );
            $stmt->execute([
                "ip_blocked_{$this->ip}",
                "subnet_blocked_{$this->subnetKey()}",
            ]);
            return $stmt->fetch() !== false;
        } catch (\PDOException $e) {
            // fail open: a broken/missing table must not take the site down
            error_log('access_control lookup failed: ' . $e->getMessage());
            return false;
        }
    }

    private function subnetKey(): string
    {
        if (str_starts_with($this->ip, 'v6:')) {
            // IPv6 /64: first 16 hex chars (64 bits)
            return 'v6:' . substr($this->ip, 3, 16);
        }
        // IPv4 /24: first three octets
        $parts = explode('.', substr($this->ip, 3));
        return 'v4:' . $parts[0] . '.' . $parts[1] . '.' . $parts[2];
    }

    /**
     * Atomic increment on a shared counter. An expired row restarts at 1,
     * matching the old APCu behaviour where an expired key simply vanished.
     * Returns the new count; 0 on DB failure (fail open).
     */
    private function bumpCounter(string $key, int $ttl): int
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO access_control (ac_key, ac_count, ac_expires) " .
                "VALUES (?, 1, UNIX_TIMESTAMP() + ?) " .
                "ON DUPLICATE KEY UPDATE " .
                "ac_count = IF(ac_expires <= UNIX_TIMESTAMP(), 1, ac_count + 1), " .
                "ac_expires = VALUES(ac_expires)"
            );
            $stmt->execute([$key, $ttl]);
            $stmt = $this->db->prepare(
                "SELECT ac_count FROM access_control WHERE ac_key = ?"
            );
            $stmt->execute([$key]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log('access_control counter failed: ' . $e->getMessage());
            return 0;
        }
    }

    private function recordSubnetTrapHit(): void
    {
        $subnet = $this->subnetKey();
        $hits = $this->bumpCounter("subnet_trap_hits_{$subnet}", self::TRAP_WINDOW);

        if ($hits >= self::SUBNET_BLOCK_THRESHOLD) {
            try {
                // remember what tripped the counter so the admin page
                // can show it
                $stmt = $this->db->prepare(
                    "INSERT INTO access_control (ac_key, ac_count, ac_expires, ac_info) " .
                    "VALUES (?, 1, UNIX_TIMESTAMP() + ?, ?) " .
                    "ON DUPLICATE KEY UPDATE " .
                    "ac_expires = VALUES(ac_expires), ac_info = VALUES(ac_info)"
                );
                $stmt->execute([
                    "subnet_blocked_{$subnet}",
                    self::SUBNET_BLOCK_TTL,
                    time() . ' ' . ($_SERVER['REQUEST_URI'] ?? ''),
                ]);
            } catch (\PDOException $e) {
                error_log('access_control subnet block failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * A single trap hit only earns a 403 — real users sometimes follow the
     * visible "data access" link. A strike (and thus an IP ban) is only
     * applied from the second trap hit within TRAP_WINDOW.
     */
    private function applyTrapHit(): bool
    {
        $hits = $this->bumpCounter("trap_hits_{$this->ip}", self::TRAP_WINDOW);
        // hits by logged-in users or valid token holders don't count
        // toward the subnet threshold — they're allowed to follow the link
        if (!($this->has_authenticated_user || $this->hasValidToken())) {
            $this->recordSubnetTrapHit();
        }
        if ($hits >= 2) {
            self::blockIp($this->db, $this->ip);
            return true;
        }
        return false;
    }

    /**
     * Active subnet blocks for the admin "access control" page:
     * subnet key => ['ttl' => seconds until expiry,
     * 'trigger' => "timestamp request-uri" or null].
     */
    public static function listBlocks(DBConnector $db): array
    {
        $blocks = [];
        try {
            // prune dead rows — cheap here because admin visits are rare
            $db->query(
                "DELETE FROM access_control WHERE ac_expires <= UNIX_TIMESTAMP()"
            );
            $stmt = $db->query(
                "SELECT ac_key, ac_expires, ac_info FROM access_control " .
                "WHERE ac_key LIKE 'subnet\_blocked\_%' " .
                "AND ac_expires > UNIX_TIMESTAMP() ORDER BY ac_key"
            );
            foreach ($stmt->fetchAll() as $row) {
                $blocks[substr($row['ac_key'], strlen('subnet_blocked_'))] = [
                    'ttl' => max(0, (int)$row['ac_expires'] - time()),
                    'trigger' => $row['ac_info'],
                ];
            }
        } catch (\PDOException $e) {
            error_log('access_control list failed: ' . $e->getMessage());
        }
        return $blocks;
    }

    /**
     * Record a strike and (re)block the IP for an escalating duration:
     * 15min → 24h → 7d → 30d. Strikes decay after 90 quiet days.
     */
    public static function blockIp(DBConnector $db, string $ip): void
    {
        $ip = self::normalizeIp($ip);
        try {
            $stmt = $db->prepare(
                "INSERT INTO access_control (ac_key, ac_count, ac_expires) " .
                "VALUES (?, 1, UNIX_TIMESTAMP() + ?) " .
                "ON DUPLICATE KEY UPDATE " .
                "ac_count = IF(ac_expires <= UNIX_TIMESTAMP(), 1, ac_count + 1), " .
                "ac_expires = VALUES(ac_expires)"
            );
            $stmt->execute(["ip_strikes_{$ip}", self::STRIKES_TTL]);
            $stmt = $db->prepare(
                "SELECT ac_count FROM access_control WHERE ac_key = ?"
            );
            $stmt->execute(["ip_strikes_{$ip}"]);
            $strikes = (int)$stmt->fetchColumn();

            $ttl = self::STRIKE_TTLS[$strikes - 1] ?? self::MAX_BLOCK_TTL;
            $stmt = $db->prepare(
                "INSERT INTO access_control (ac_key, ac_count, ac_expires) " .
                "VALUES (?, 1, UNIX_TIMESTAMP() + ?) " .
                "ON DUPLICATE KEY UPDATE ac_expires = VALUES(ac_expires)"
            );
            $stmt->execute(["ip_blocked_{$ip}", $ttl]);
        } catch (\PDOException $e) {
            error_log('access_control blockIp failed: ' . $e->getMessage());
        }
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