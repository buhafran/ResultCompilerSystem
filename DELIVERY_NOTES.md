# Result System v1.2.2 Delivery Notes

This release adds bulk upload support for classes and subjects, multiple-subject creation for class subjects and teacher assignments, bulk AI comment generation, Filament navigation group typing fixes, API rate limiter registration, and the School Membership `orWhereKey()` compatibility fix.

It also includes the Docker build correction from v1.2.1 so the production image installs Composer dependencies with required PHP extensions such as `intl`.

## After deployment

Run:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan optimize
```

For Docker:

```bash
docker compose --env-file deploy/.env -f deploy/docker-compose.yml build app
docker compose --env-file deploy/.env -f deploy/docker-compose.yml up -d --no-build --force-recreate app queue scheduler nginx
```

The new class and subject import templates are available from the Classes and Subjects resource pages.
