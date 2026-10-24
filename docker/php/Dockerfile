ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm

# vici.org needs: PDO MySQL (app/DB/DBConnector.php), mbstring (app/Identifiers, User),
# APCu (app/Security/AccessControl.php), fileinfo is bundled.
# opcache for production-like performance.
# libonig-dev is required by mbstring (oniguruma) on PHP 8.x.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql mbstring opcache exif \
    && pecl install apcu \
    && docker-php-ext-enable apcu

WORKDIR /var/www/local.vici.novo
