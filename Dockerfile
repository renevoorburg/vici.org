FROM php:7.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
        nginx \
        supervisor \
        curl \
        wget \
        unzip \
        libonig-dev \
        libcurl4-openssl-dev \
        libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions needed for vici.org
RUN docker-php-ext-install mysqli pdo pdo_mysql exif mbstring curl \
    && pecl install apcu \
    && docker-php-ext-enable apcu

# Configure PHP
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini

# Copy nginx configuration
COPY nginx/default.conf /etc/nginx/sites-available/default.conf
RUN ln -sf /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default

# Copy supervisord configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create web root directory
RUN mkdir -p /var/www/org.vici/public

# Copy application files
COPY public/ /var/www/org.vici/public/

# Set permissions
RUN chown -R www-data:www-data /var/www/org.vici

# Expose port
EXPOSE 80

# Start supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
