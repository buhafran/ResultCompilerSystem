# Result System v1.2.1 Docker Build Fix

This patch fixes the Docker build after v1.2.0 restored the older Dockerfile.

## What it fixes

- Composer now runs inside a PHP image with `ext-intl`, which Filament 5 requires.
- `composer.*` is copied instead of only `composer.json`, so `composer.lock` is used when available.
- If `composer.lock` is missing, the image can resolve dependencies once, then you should copy the generated lock file back into `backend/composer.lock` and commit it.
- Queue and scheduler reuse the already-built local application image instead of rebuilding or pulling `result-system-app`.
- Stale Laravel cache files are removed so missing dev packages such as Laravel Pail are not loaded in production.
- Entry point runs migrations before `optimize:clear` to avoid database-cache table errors on fresh databases.
- Docker Nginx preserves forwarded HTTPS headers from Apache for Livewire URL generation.

## Files to replace

Copy these files into your project:

- `backend/Dockerfile`
- `backend/docker/entrypoint.sh`
- `backend/.dockerignore`
- `deploy/docker-compose.yml`
- `deploy/nginx/default.conf`
- `Makefile`

## Commands

From the project root:

```bash
DC="docker compose --env-file deploy/.env -f deploy/docker-compose.yml"

$DC down --remove-orphans
$DC build --no-cache app
$DC up -d --no-build
$DC logs --tail=200 app
```

Do not use `down -v` unless you intentionally want to delete the MySQL volume.

## After successful build, save composer.lock

If the project still has no lock file, copy it from the built image/container and commit it:

```bash
$DC cp app:/var/www/html/composer.lock backend/composer.lock
ls -lh backend/composer.lock
git add backend/composer.lock
git commit -m "Add composer lock file for reproducible production builds"
```

Then future builds will install exact locked versions rather than resolving dependencies on the server.
