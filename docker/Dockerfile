# RRHH El Sol - Laravel 12 + Filament 3 app image
# Base: PHP 8.3 FPM (Debian bookworm for reliable extension builds)
FROM php:8.3-fpm-bookworm

# System packages and PHP extensions required by Laravel 12 + Filament 3.
# Uses mlocati/docker-php-extension-installer for reproducible extension builds.
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
    && install-php-extensions \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        intl \
        zip \
        exif \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Match the host user id to avoid permission issues on the bind mount (dev).
ARG UID=1000
ARG GID=1000
RUN groupmod -g ${GID} www-data 2>/dev/null || true \
    && usermod -u ${UID} -g ${GID} www-data 2>/dev/null || true

WORKDIR /var/www/html

# PHP-FPM listens on 9000 for nginx.
EXPOSE 9000

CMD ["php-fpm"]
