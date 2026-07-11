#!/bin/sh
set -eu

cd /var/www/html

echo "Preparing Laravel runtime..."

# Refresh the shared public volume from the current image on every deployment.
if [ -d /opt/result-public ]; then
  cp -a /opt/result-public/. /var/www/html/public/
fi

# Prevent stale development caches from loading missing dev providers.
rm -f \
  bootstrap/cache/config.php \
  bootstrap/cache/packages.php \
  bootstrap/cache/services.php \
  bootstrap/cache/events.php \
  bootstrap/cache/routes-v7.php

mkdir -p \
  storage/app/public \
  storage/app/private \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

php artisan package:discover --ansi
php artisan filament:assets >/dev/null 2>&1 || true
php artisan storage:link --force >/dev/null 2>&1 || true

# Run migrations before optimize:clear so database-backed cache/session tables exist first.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
