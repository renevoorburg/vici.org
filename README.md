# Vici.org

Codebase for [https://vici.org](https://vici.org).

## Running with Docker

Two compose setups are included:

- `docker-compose.local.yml` — local development stack (nginx + PHP-FPM +
  MariaDB seeded from `db/`), reachable at http://localhost:8080.
- `docker-compose.yml` — server stack for e.g. preview.vici.org or
  production on the host (nginx + PHP-FPM, no own database by default).
  Preview vs production is selected purely by the env file passed via
  `--env-file`.

### Local development

After a fresh checkout, init the photoswipe submodules first:

```
git submodule update --init --recursive
docker compose -f docker-compose.local.yml up -d --build
```

This starts three containers: `web` (nginx), `php` (PHP-FPM) and `mysql`
(MariaDB). On first start MariaDB loads the dumps mounted from `db/`:

* `geo.sql.gz` — data for the reverse geocoder (place names on `/vici/{id}`
  pages), derived from geonames; a subset covering European countries like
  DE, NL, BE, IT, FR, ES.
* `vici.sql.gz` — a dump of the vici.org data with anonymized user data.

Configuration for the stack lives in `.env.docker` (mounted into the php
container as `.env`). It contains development-only values, including the
Cloudflare Turnstile test keys that always pass.

A development account is seeded: username `user1`, password `vici-dev`.

### Preview on the production server

`docker-compose.yml` runs the site behind the host nginx that
terminates TLS for preview.vici.org (see `nginx/preview.vici.org.conf`,
which proxies to `127.0.0.1:8081`).

Setup on the server:

1. Check out this repository and the submodules:
   `git submodule update --init --recursive` (photoswipe assets under
   `public/js/` are submodules).
2. `cp .env.preview.example .env.preview` and fill in real values.
3. Install the PHP dependencies (composer runs inside the php image, so
   its platform check always matches the runtime — the host's own PHP
   version is irrelevant):

   ```
   docker compose run --rm php \
     composer install --no-dev --prefer-dist
   ```
4. Put the database dumps in `./db-preview/` (gitignored): `.sql` or
   `.sql.gz` files, imported in alphabetical order on first init —
   typically a `vici` dump, a `geo` dump and a `zz-grants.sql` granting
   the app user read access to `geo`:

   ```sql
   GRANT SELECT ON `geo`.* TO 'vici'@'%';
   FLUSH PRIVILEGES;
   ```

   The stack always runs its own MariaDB (`DB_HOST=mysql`); the mysql
   service reuses `DB_USER`/`DB_PASS`/`DB_MAIN` from the env file for the
   account it creates. Wait until `vici-preview-mysql` reports healthy
   before browsing; importing a full dump takes a while. To re-import,
   remove the `vici-preview-db-data` volume.

   Toponyms (country + nearby place) are materialized on `points`
   (`pnt_country`/`pnt_place`). Dumps containing data predate those
   columns, so after importing a dump that lacks them run the backfill:

   ```
   docker exec vici-preview-php php bin/backfill_toponyms.php
   ```

   It fills `pnt_country` (ISO code — localized via `geo.countries`) and
   `pnt_place` via reverse geocoding. Re-run it after a geonames data
   refresh; points saved through the editor get the same fields on save.

5. Uploaded images can go to two places:

   **a) S3-compatible object storage** (e.g. TransIP Object Store) — set
   `S3_ENDPOINT`, `S3_REGION`, `S3_BUCKET`, `S3_ACCESS_KEY` and
   `S3_SECRET_KEY` in `.env.preview`. Objects are stored under the
   `uploads/` prefix (configurable via `S3_PREFIX`) so keys match the
   `/uploads/<file>` paths stored in the database. No `ACL` is set per
   object: make the container public via its ACL for now, so
   images.vici.org can fetch originals over HTTP. Once the image proxy
   can fetch private objects with credentials, the public ACL can be
   switched off at the container level.

   **b) Local filesystem** — without `S3_BUCKET` uploads go to
   `public/uploads`, bind-mounted from `PROD_UPLOADS_DIR` (default
   `/var/www/org.vici.static/public/uploads`, where main and
   images.vici.org serve them). The php container writes as `www-data`
   (uid 33), so that dir must be writable by it. For full isolation from
   production, point `PROD_UPLOADS_DIR` at a local dir.

   Either way the HTML only ever references `//images.vici.org` URLs;
   there is no client-side fallback to the storage location.

6. Migrate existing images to object storage — `img_path` values in the
   database are not all `/uploads/...`: a large set is `/livius/...` (with
   subdirectories). The static root is bind-mounted read-only at
   `/srv/static` in the php container (`PROD_STATIC_DIR`), so one command
   covers every prefix. The object key is the path relative to that root,
   i.e. `img_path` minus the leading slash:

   ```
   docker compose --env-file .env.preview run --rm php \
     php bin/sync-images-to-s3.php --dry-run /srv/static uploads livius
   docker compose --env-file .env.preview run --rm php \
     php bin/sync-images-to-s3.php /srv/static uploads livius
   ```

   The subdir arguments restrict the sync to those top-level trees;
   without them everything under the source root is copied (including
   any unrelated static files). Objects that already exist are skipped,
   so the run is resumable. Re-run it after fixing any `FAILED` lines.

7. Account-activation mails are sent via `mail()` → `msmtp` inside the php
   container. The image carries a default `/etc/msmtprc` that relays to
   the Postfix on the host via `host.docker.internal:25` — for that, the
   host Postfix must listen beyond loopback (`inet_interfaces`) and allow
   relay from the docker subnet (`mynetworks`, e.g. `172.16.0.0/12`).
   Mount a custom `msmtprc` over `/etc/msmtprc` for an authenticated
   SMTP relay instead (see the commented mount in `docker-compose.yml`),
   and set `MAIL_FROM` in `.env.preview`. Without a reachable relay,
   registration still succeeds but the mail failure is only logged.

Start the stack:

```
docker compose --env-file .env.preview up -d --build
```

## Running without Docker

You need a database (MariaDB / MySQL), a webserver (nginx config examples
in `nginx/`) and PHP 8. Load the `db/` dumps into the database.

The PHP code reads its configuration from environment variables or a `.env`
file in the repo root (via phpdotenv). See `.env.docker` for the required
keys; `nginx/fastcgi_params` shows the older fastcgi-param style.

If set, the environment variable `VICIBASE` is passed to the `vici.js`
javascript widget, which uses it as the base url for data requests. So if
your local instance runs as http://vici.local, set `VICIBASE` accordingly.

## Tiles

The tiles used in Vici.org are served by [tileproxy](https://github.com/renevoorburg/tileproxy).

## Images

The images served by Vici.org are processed and cached by [imageserver](https://github.com/renevoorburg/imageserver).
