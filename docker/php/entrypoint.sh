#!/bin/sh
set -e

# var/ is gitignored so a fresh checkout lacks Smarty's dirs; the repo is
# bind-mounted over the workdir, so create them here (they land on the
# host) and make them writable for the php-fpm pool user.
mkdir -p var/templates_cache var/templates_compile
chown -R www-data:www-data var

exec docker-php-entrypoint "$@"
