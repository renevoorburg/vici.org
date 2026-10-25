ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm

# vici.org needs: PDO MySQL (app/DB/DBConnector.php), mbstring (app/Identifiers, User),
# APCu (app/Security/AccessControl.php), fileinfo is bundled.
# opcache for production-like performance.
# libonig-dev is required by mbstring (oniguruma) on PHP 8.x.
# msmtp provides a sendmail binary for mail() — configure via a mounted
# /etc/msmtprc on hosts that should actually send mail; without it mail()
# fails gracefully (logged by app/Mail/Mailer.php).
RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev msmtp unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql mbstring opcache exif \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && echo 'sendmail_path = "/usr/bin/msmtp -t -i"' > /usr/local/etc/php/conf.d/msmtp.ini

# default mail relay: Postfix on the docker host (no credentials baked in;
# mount a custom file over /etc/msmtprc for authenticated relay setups)
COPY docker/php/msmtprc /etc/msmtprc

# composer inside the image runs on the same PHP as the runtime,
# so platform checks always match (vendor/ lives on the repo mount)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/entrypoint.sh /usr/local/bin/vici-entrypoint.sh
RUN chmod +x /usr/local/bin/vici-entrypoint.sh

WORKDIR /var/www/local.vici.novo

ENTRYPOINT ["/usr/local/bin/vici-entrypoint.sh"]
