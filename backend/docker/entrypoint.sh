#!/bin/sh

set -eu

cd /var/www/html

echo "Preparing Laravel directories..."

rm -f \
    bootstrap/cache/config.php \
    bootstrap/cache/packages.php \
    bootstrap/cache/services.php \
    bootstrap/cache/events.php

mkdir -p \
    bootstrap/cache \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data \
    bootstrap/cache \
    storage

echo "Discovering packages..."

php artisan package:discover --ansi
php artisan filament:assets
php artisan storage:link --force || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."

    php artisan migrate --force
fi

echo "Clearing and rebuilding Laravel caches..."

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Laravel startup completed."

exec "$@"
