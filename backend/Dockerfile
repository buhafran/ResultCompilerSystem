# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2

FROM composer:${COMPOSER_VERSION} AS composer-bin

# Install the same PHP extensions in both the Composer build stage and runtime.
# This prevents Composer from resolving packages against a different platform.
FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        simplexml \
        xml \
        xmlwriter \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

# PHP dependencies.
FROM php-base AS vendor
WORKDIR /app

# composer.* copies composer.json and composer.lock when the lock file exists.
COPY composer.* ./

RUN set -eux; \
    if [ -f composer.lock ]; then \
        composer install \
            --no-dev \
            --no-interaction \
            --no-progress \
            --prefer-dist \
            --no-scripts \
            --no-autoloader; \
    else \
        echo >&2 'WARNING: composer.lock is missing. Resolving dependencies once so the image can build.'; \
        echo >&2 'Copy /var/www/html/composer.lock from the built app container back into the source tree and commit it.'; \
        composer update \
            --no-dev \
            --no-interaction \
            --no-progress \
            --prefer-dist \
            --no-scripts \
            --no-autoloader; \
    fi

# Front-end assets.
FROM node:22-bookworm-slim AS frontend
WORKDIR /app

COPY package*.json ./
COPY vite.config.js ./
COPY resources ./resources

RUN set -eux; \
    if [ -f package-lock.json ]; then \
        npm ci --no-audit --no-fund; \
    else \
        npm install --no-audit --no-fund; \
    fi; \
    npm run build

# Production application image.
FROM php-base AS runtime
WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.lock ./composer.lock
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload \
        --no-dev \
        --classmap-authoritative \
        --no-scripts \
        --no-interaction

# Copy after the application so the intended entrypoint always wins.
COPY docker/entrypoint.sh /usr/local/bin/result-entrypoint

RUN chmod +x /usr/local/bin/result-entrypoint \
    && mkdir -p \
        storage/app/public \
        storage/app/private \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        /opt/result-public \
    && cp -a public/. /opt/result-public/ \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
        public \
        /opt/result-public

USER www-data
# Never copy Laravel caches created on the development machine.
RUN rm -f bootstrap/cache/*.php \
    && mkdir -p \
        bootstrap/cache \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data \
        bootstrap/cache \
        storage

ENTRYPOINT ["result-entrypoint"]
CMD ["php-fpm"]
