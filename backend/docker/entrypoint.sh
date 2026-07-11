#!/bin/sh
set -eu
cd /var/www/html

# Refresh the shared public volume from the current image on every deployment.
if [ -d /opt/result-public ]; then
  cp -a /opt/result-public/. /var/www/html/public/
fi

php artisan package:discover --ansi
php artisan filament:assets >/dev/null 2>&1 || true
php artisan storage:link --force >/dev/null 2>&1 || true
php artisan optimize:clear
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi
php artisan config:cache
php artisan route:cache
php artisan view:cache
exec "$@"
