# Production Deployment

## Option A: Docker Compose

### Requirements

- Ubuntu 24.04 or equivalent
- Docker Engine with the Compose plugin and BuildKit
- A domain name and an HTTPS reverse proxy/load balancer
- At least 2 CPU cores, 4 GB RAM and adequately sized encrypted storage for the initial rollout

### 1. Prepare environment files

```bash
cp backend/.env.example backend/.env
cp deploy/.env.example deploy/.env
```

Generate an application key without printing production secrets into shell history where possible:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Set the output as `APP_KEY` in `backend/.env`. Set:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://results.example.edu
DB_HOST=mysql
DB_DATABASE=result_system
DB_USERNAME=result_user
DB_PASSWORD=the-same-strong-password-used-in-deploy-env
SESSION_SECURE_COOKIE=true
GEMINI_API_KEY=optional-server-side-key
```

Set matching database values in `deploy/.env`.

### 2. Start services

```bash
make up
make logs
```

The application container runs migrations because `RUN_MIGRATIONS=true`. After the first stable deployment, many teams change this to `false` and run migrations explicitly during a controlled release.

### 3. Create the first administrator

```bash
docker compose --env-file deploy/.env -f deploy/docker-compose.yml exec app \
  php artisan platform:make-admin owner@example.edu --name="Platform Owner"
```

Open `/platform`, create the first school, and create/assign its school administrator.

### 4. HTTPS

Expose the Nginx container only through an HTTPS proxy such as Cloudflare, Caddy, Traefik or a host Nginx/Certbot setup. Forward `X-Forwarded-Proto`, `X-Forwarded-For` and host headers. Do not expose MySQL publicly.

### 5. Backup

At minimum:

- nightly encrypted MySQL backups;
- uploaded files from the `app_storage` volume;
- off-server retention with tested restore;
- a pre-migration backup before every schema release.

## Option B: Conventional Ubuntu deployment

Install PHP 8.3+, required extensions, Composer 2, Node 22, MySQL 8 and Nginx. Then:

```bash
cd backend
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Serve `backend/public` as the web root. Run one systemd/Supervisor worker:

```bash
php artisan queue:work --sleep=2 --tries=3 --timeout=120
```

Run the scheduler every minute:

```cron
* * * * * cd /var/www/result-system/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Teacher app

```bash
cd mobile
npm install
npx expo install --fix
```

Create `.env`:

```dotenv
EXPO_PUBLIC_API_URL=https://results.example.edu/api/v1
```

Development:

```bash
npx expo start
```

Install EAS CLI and create an internal Android APK:

```bash
npm install -g eas-cli
eas login
eas build --platform android --profile preview
```

Before store release, replace the example Android package and iOS bundle identifier in `app.json`, configure icons/splash assets, privacy disclosures and signing accounts.

## Release procedure

1. Back up database and storage.
2. Deploy/build new images.
3. Run migrations once.
4. Clear/cache configuration, routes and views.
5. Restart queue workers.
6. Run smoke tests: `/up`, platform login, tenant login, one API roster and one student result.
7. Monitor logs and failed jobs.
