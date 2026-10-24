# Vici.org

Codebase for [https://vici.org](https://vici.org).

## Running with Docker

Two compose setups are included:

- `docker-compose.yml` — local development stack (nginx + PHP-FPM + MariaDB
  seeded from `db/`), reachable at http://localhost:8080.
- `docker-compose.preview.yml` — preview stack for e.g. preview.vici.org on
  the production server (nginx + PHP-FPM, no own database by default).

### Local development

```
docker compose up -d --build
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

`docker-compose.preview.yml` runs the site behind the host nginx that
terminates TLS for preview.vici.org (see `nginx/preview.vici.org.conf`,
which proxies to `127.0.0.1:8081`).

Setup on the server:

1. Check out this repository.
2. `cp .env.preview.example .env.preview` and fill in real values.
3. Install the PHP dependencies (composer runs inside the php image, so
   its platform check always matches the runtime — the host's own PHP
   version is irrelevant):

   ```
   docker compose -f docker-compose.preview.yml run --rm php \
     composer install --no-dev --prefer-dist
   ```
4. Choose a database mode:

   **a) Use the production database on the host** — set
   `DB_HOST=host.docker.internal`. The compose file maps that name to the
   docker bridge gateway. This requires mysqld to also listen on the bridge
   interface; a `bind-address` of `127.0.0.1` only will not work. Widen it
   (e.g. `0.0.0.0` or the docker0 address) or point `DB_HOST` at an address
   MySQL listens on.

   **b) Use a containerized database with a fresh dump** — put `.sql` or
   `.sql.gz` files in `./db-preview/` (gitignored; they run in alphabetical
   order on first init), set `DB_HOST=mysql` plus matching credentials in
   `.env.preview`, and start with the `localdb` profile:

   ```
   docker compose -f docker-compose.preview.yml --profile localdb up -d --build
   ```

   Wait until `vici-preview-mysql` reports healthy before browsing;
   importing a full dump takes a while. To re-import, remove the
   `vici-preview-db-data` volume.

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
   docker compose -f docker-compose.preview.yml exec php \
     php bin/sync-images-to-s3.php --dry-run /srv/static uploads livius
   docker compose -f docker-compose.preview.yml exec php \
     php bin/sync-images-to-s3.php /srv/static uploads livius
   ```

   The subdir arguments restrict the sync to those top-level trees;
   without them everything under the source root is copied (including
   any unrelated static files). Objects that already exist are skipped,
   so the run is resumable. Re-run it after fixing any `FAILED` lines.

7. Account-activation mails are sent via `mail()` → `msmtp` inside the php
   container. Mount an `msmtprc` at `/etc/msmtprc` (commented-out example in
   `docker-compose.preview.yml`) so it can reach an SMTP relay, and set
   `MAIL_FROM` in `.env.preview`. Without it, registration still succeeds
   but the mail failure is only logged.

Start without the containerized database (mode a):

```
docker compose -f docker-compose.preview.yml up -d --build
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
