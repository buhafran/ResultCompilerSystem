# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2

FROM composer:${COMPOSER_VERSION} AS composer-bin

# Shared PHP base used by Composer, PHP-FPM, queue and scheduler.
# Filament 5 requires ext-intl, so Composer must run in an image that already has intl.
FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

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

FROM php-base AS vendor
WORKDIR /app

# Copy composer.lock when present. If it is missing, the image can still build once,
# but you should copy the generated lock file back to source and commit it.
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
        echo >&2 'WARNING: composer.lock is missing. Resolving dependencies once.'; \
        echo >&2 'After a successful build, copy /var/www/html/composer.lock back into backend/composer.lock and commit it.'; \
        composer update \
            --no-dev \
            --no-interaction \
            --no-progress \
            --prefer-dist \
            --no-scripts \
            --no-autoloader; \
    fi

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

FROM php-base AS runtime
WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.lock ./composer.lock
COPY --from=frontend /app/public/build ./public/build

# Remove development/stale Laravel caches that may reference dev packages such as Laravel Pail.
RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload \
        --no-dev \
        --classmap-authoritative \
        --no-scripts \
        --no-interaction

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

ENTRYPOINT ["result-entrypoint"]
CMD ["php-fpm"]
