#!/usr/bin/env bash

set -Eeuo pipefail

cd "$(dirname "$0")"

DC="docker compose --env-file deploy/.env -f deploy/docker-compose.yml"
BRANCH="${1:-main}"
BACKUP_DIR="backups"

mkdir -p "$BACKUP_DIR"

echo "Creating database backup..."

$DC exec -T mysql sh -lc '
    exec mysqldump \
        --single-transaction \
        --quick \
        --lock-tables=false \
        -uroot \
        -p"$MYSQL_ROOT_PASSWORD" \
        "$MYSQL_DATABASE"
' > "$BACKUP_DIR/result_system_$(date +%Y%m%d_%H%M%S).sql"

echo "Fetching repository updates..."

git fetch origin
git pull --ff-only origin "$BRANCH"

echo "Building application image..."

$DC build app

echo "Enabling maintenance mode..."

$DC exec app php artisan down \
    --retry=60 \
    --refresh=15 || true

echo "Running pending migrations..."

$DC run --rm --no-deps \
    --entrypoint php \
    app artisan migrate --force

echo "Recreating application services..."

$DC up -d \
    --no-build \
    --force-recreate \
    app queue scheduler nginx

echo "Optimizing Laravel..."

$DC exec app sh -lc '
    php artisan optimize:clear &&
    php artisan package:discover --ansi &&
    php artisan filament:assets &&
    php artisan optimize &&
    php artisan queue:restart &&
    php artisan up
'

echo "Checking containers..."

$DC ps

echo "Deployment completed."