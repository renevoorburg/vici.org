# Access control & anti-bot logic

All automatic blocking and rate limiting lives in
`app/Security/AccessControl.php`. Every request passes through
`AccessControl::enforceAndRun()`, which runs a fixed pipeline of checks
before the route handler executes.

## Request pipeline (in order)

1. **Block check** — `ip_blocked_*` or `subnet_blocked_*` row in
   `access_control` → 403
2. **Token requirement** — route with `setRequiresToken()` and no valid
   token → 403
3. **Trap link** — `/data-access/*` handling (see below)
4. **Global bot limits** — two caps for self-declared bots: 10 req/60s
   per IP (APCu) plus a shared 60 req/60s budget per user-agent
   (MariaDB) so rotating IPs can't dodge the cap
5. **Subnet scraper detection** — page-view vs geojson asymmetry (below)
6. **Anonymous rate limit** — APCu counter, 8 req/60s per IP+route
7. **Bot-attracting link limit** — APCu counter, currently disabled
   (call sites commented out in `controller.php`)
8. **Authentication requirements** — `setRequiresAuthenticatedUser()` or
   `setRequiresAuthOrToken()` → redirect to `/login`, or 403 for
   self-declared bots
9. Route handler runs

Self-declared bots (UA containing `bot`, `crawler` or `spider`) get bare
403/429 responses; browser-like UAs get redirected to `/login?wait=…` or
`/login?loginrequired=…` so real users keep a path in.

## The trap link (`/data-access/{id}`)

`/data-access` is disallowed in `robots.txt`. Anything that follows it is
either a disobedient crawler or a curious human.

- **First hit** (per IP, 24h window): counted, but the visitor gets a
  `301` to `/` with `X-Robots-Tag: noindex` — real users bounce home
  harmlessly, search engines learn the URL moved.
- **Second hit** within 24h: a strike — the IP gets an escalating block
  (see strikes below) and a 403.
- **Subnet counter**: every anonymous trap hit also bumps
  `subnet_trap_hits_{subnet}`. At **20 hits within 24h** the whole subnet
  gets `subnet_blocked` for 24h. Logged-in users and valid token holders
  still earn personal strikes, but do **not** count toward the subnet
  threshold (they're allowed to follow the link).
- The honeypot submission on the contact form calls `blockIp()`
  directly (independent of this counter).

## Subnet scraper detection (page-view/geojson asymmetry)

Distributed scrapers pull `/vici/{id}` HTML but never load the map.
Real browsers always load `geojson.php` when viewing a page.

- `geojson.php` bumps `subnet_geojson_hits_{subnet}` — any hit
  legitimizes the whole subnet.
- `/vici/{id}` page views bump `subnet_page_views_{subnet}`, but only:
  - when `subnet_geojson_hits` is **0** (once anyone in the subnet loads
    a map, counting stops entirely — a Ziggo/ISP `/24` with one real
    visitor can never trip this),
  - for anonymous requests without a valid token,
  - for non-bot UAs (honest crawlers like Bingbot that don't render JS
    are exempt via `isAdvertisingBot()`).
- At **20 page views in 24h with zero geojson hits** →
  `subnet_blocked` for 24h + 403.

## Subnets and keys

IPv4 is grouped by `/24`, IPv6 by `/64` (`v6:` + first 64 bits hex).
Deliberately conservative: `/16`-style aggregation was considered and
rejected as too broad.

## Strikes (per-IP escalation)

`blockIp()` records a strike in `ip_strikes_*` (decays after 90 quiet
days) and sets `ip_blocked_*` with an escalating TTL:

| Strike | Block duration |
|--------|----------------|
| 1      | 15 minutes     |
| 2      | 24 hours       |
| 3      | 7 days         |
| 4+     | 30 days        |

## Rate limiting (APCu — intentionally per-worker)

`bot_rate_limited_{ip}` — 10 requests per 60s across **all routes** for
self-declared bots (UA contains `bot`/`crawler`/`spider`) without a
session or valid token. Checked before any route-specific logic; over
the cap → `429 + Retry-After`. Honest crawlers like Googlebot crawl well
below this and additionally render the map, which also legitimizes their
subnet for scraper detection.

`bot_ua_rate_{sha1(ua)}` — 60 requests per 60s **per user-agent string**
(lowercased, hashed), also across all routes. Stored in
`access_control`, not APCu: the shared MariaDB state means a crawler
rotating through hundreds of IPs still burns through one budget. The
cap is deliberately looser than the per-IP cap — a pool of IPs sending
one UA is suspicious, but a single busy IP from a real crawler is not.

`anon_rate_limited_{route}_{ip}` — 8 requests per 60s per route for
anonymous requests. On exceeding: bots get `429 + Retry-After`, browsers
get redirected to `/login?wait=60` and the counter is lowered by 2
(`apcu_dec`) so humans regain access quickly.

`botlike_rate_limited_{ip}` — 3 requests per 15min for "bot-attracting"
endpoints. Mechanism exists but all call sites are commented out —
dead code kept as an option.

These counters live in APCu deliberately: rate limiting doesn't need
cross-worker precision, and keeping them off the database avoids a write
on every anonymous request.

## Storage

All block/counter state except the two APCu rate limiters lives in the
`access_control` table (`db/access_control.sql`):

| Key pattern | Content |
|-------------|---------|
| `ip_blocked_{ip}` | block, `ac_expires` = unblock time |
| `ip_strikes_{ip}` | strike count, decays after 90d |
| `subnet_blocked_{subnet}` | block; `ac_info` = `"timestamp request-uri"` of the trigger |
| `subnet_trap_hits_{subnet}` | trap-hit counter, 24h window |
| `subnet_page_views_{subnet}` | page views while geojson count = 0 |
| `subnet_geojson_hits_{subnet}` | map loads |
| `trap_hits_{ip}` | per-IP trap-hit counter, 24h window |

Counters use atomic `INSERT … ON DUPLICATE KEY UPDATE`; an expired row
restarts at 1 (same semantics as an expired APCu key). All database
calls **fail open**: a missing/broken table logs `error_log` and denies
nothing — the site stays up, protection degrades silently.

Shared MariaDB storage matters because APCu is per php-fpm worker: a
block in worker memory only covered ~1/N of requests and was invisible
to the admin page. Blocks now apply to all workers and survive
container restarts.

`AccessControlPage::listBlocks()` prunes expired rows on each admin
visit — cheap because admin traffic is rare.

## Tokens

`hasValidToken()` accepts, in order:

- `X-Vici-Token` header == `VICITOKEN` env value
- `X-Vici-Token` header == any of `CUST1`–`CUST9`
- User-Agent starting with any of `UA1`–`UA9` prefixes
- `EXTSECRET` present as a complete `&param=value&` segment in the query
  string (this is how the site's own map JS loads geojson)

Valid tokens grant access to `requires_auth_or_token` routes
(kml/rdf/geojson) and exempt the request from subnet counters — they
never grant edit rights (`requires_authenticated_user` still needs a
session login).

## Admin page

`/accesscontrol` (level-5 admins only) lists active subnet blocks with
remaining TTL and the triggering timestamp + URL. Adjacent IPv4 `/24`
blocks are compressed into minimal CIDR ranges (two /24s display as a
single `/23`). Individual IP blocks, strikes and raw counters are
deliberately not shown.

## Tunables

All in `AccessControl.php`:

```php
RATE_LIMIT_SECONDS          = 60    // APCu window (anon + bot)
MAX_ANONYMOUS_REQUESTS      = 8     // per route+IP
MAX_BOT_REQUESTS            = 10    // per IP, all routes, bot UAs
MAX_BOT_UA_REQUESTS         = 60    // per UA string, all routes (DB)
MAX_BOTLIKE_REQUESTS        = 3     // disabled mechanism
TRAP_WINDOW                 = 86400 // 24h
SUBNET_BLOCK_THRESHOLD      = 20    // trap hits → subnet block
SUBNET_PAGE_VIEW_THRESHOLD  = 20    // page views w/o geojson → block
SUBNET_BLOCK_TTL            = 86400 // 24h
STRIKE_TTLS                 = [900, 86400, 604800]
MAX_BLOCK_TTL               = 2592000  // 30d
STRIKES_TTL                 = 7776000  // 90d decay
```

## Installation

Fresh Docker stacks create `access_control` automatically — both compose
files mount `db/access_control.sql` into
`/docker-entrypoint-initdb.d/`, which MariaDB runs on first init of an
empty volume.

Existing databases need a one-off import:

```bash
docker exec -i <mysql-container> mariadb \
  -u "$DB_USER" -p"$DB_PASS" "$DB_MAIN" < db/access_control.sql
```

(Init scripts are ignored on already-initialized volumes — the manual
step is required there.)
