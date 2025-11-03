# syntax=docker/dockerfile:1

# Build a production image for phpList base-distribution (Symfony-based)
FROM php:8.1-apache-bullseye

# Set workdir
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libpng-dev libonig-dev libxml2-dev \
        libc-client2007e-dev libkrb5-dev libssl-dev libpq-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j"$(nproc)" \
        pdo pdo_mysql pdo_pgsql zip intl imap \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules and set DocumentRoot to /public
RUN a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf \
    && echo "<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>" > /etc/apache2/conf-available/phplist.conf \
    && a2enconf phplist

# Copy composer definition first and install dependencies
COPY composer.json composer.lock ./

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/usr/local/bin:${PATH}"
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Ensure config directory exists for Composer scripts that write into it
COPY config ./config

# Install PHP dependencies (include scripts so phpList creates config structure)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy the rest of the application (except files ignored by .dockerignore)
COPY . .

# Build Symfony prod cache to match the current vendor code (prevents stale container issues)
# If env vars are needed for DB during warmup, they can be provided at runtime; warmup should still succeed without DB.
RUN php bin/console cache:clear --env=prod --no-warmup || true \
    && php bin/console cache:warmup --env=prod || true

# Ensure correct permissions for cache/logs
RUN chown -R www-data:www-data var public \
    && find var -type d -exec chmod 775 {} \; \
    && find var -type f -exec chmod 664 {} \;

# Expose port and run Apache
EXPOSE 80
CMD ["apache2-foreground"]
