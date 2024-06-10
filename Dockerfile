FROM composer:latest as builder
WORKDIR /app
COPY public/composer.json public/composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist


FROM php:7.4-fpm
RUN apt-get update  \
    && apt-get install -y nginx  \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*

COPY config/nginx/default /etc/nginx/sites-available/default

COPY . /var/www
COPY --from=builder /app/vendor /var/www/public/vendor

RUN chown -R www-data:www-data /var/www

EXPOSE 80
CMD service nginx start && php-fpm