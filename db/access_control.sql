-- Shared access-control state (IP/subnet blocks, strikes, trap counters).
-- Replaces per-worker APCu storage so blocks apply to all php-fpm workers,
-- survive container restarts and are fully visible on the admin page.
--
-- Apply once on each database (before or after deploy; code fails open):
--   docker exec -i vici-preview-mysql mariadb -u "$DB_USER" -p"$DB_PASS" \
--       "$DB_MAIN" < db/access_control.sql

CREATE TABLE IF NOT EXISTS access_control (
    ac_key     VARCHAR(80)   NOT NULL PRIMARY KEY,
    ac_count   INT UNSIGNED  NOT NULL DEFAULT 0,
    ac_expires INT UNSIGNED  NOT NULL DEFAULT 0,
    ac_info    VARCHAR(255)  NULL,
    INDEX idx_expires (ac_expires)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
