# Validation Report

## Completed in this delivery

- Implemented the eight requested corrections for sliders, dashboards, memberships, terms, fresh data entry, bulk students, exports, class PDFs and platform-controlled school access.
- Updated custom action dialogs to the Filament 5 `schema()` API and checked other used APIs against the current Filament 5 documentation.
- Ran PHP syntax validation across application, configuration, migrations, routes, commands and tests.
- Validated JSON manifests, Docker Compose YAML, release-version consistency and syntax-transpiled all TypeScript/TSX application files.
- Validated dependency-free grade boundaries and competition ranking (`1, 2, 2, 4`).
- Added or retained feature tests for tenant isolation, active school access, active-term switching, CSV student import/update, term locking during compilation, public-token result access, tie ranking, release and withdrawal of an older release.
- Reviewed route authorization, tenant-scoped selectors, API school filtering, optimistic locking, offline-conflict retention, student PIN handling, AI prompt privacy, file upload storage, malformed CSV handling, CSV formula injection and deployment files.

## Required staging validation

Composer, Laravel vendor packages and mobile npm packages are not installed in the build container. Run the following on a connected staging machine before production cutover:

```bash
cd backend
composer install
php artisan migrate:fresh --seed
php artisan test
php artisan route:list
php artisan optimize

cd ../mobile
npm install
npx expo install --fix
npm run typecheck
npx expo-doctor
```

Then deploy the Docker stack and complete `docs/UAT_CHECKLIST.md` with representative platform administrators, school administrators, examination officers, teachers, students and parents. Static validation is not a substitute for staging with the actual database, queue, storage, web server and mobile build.
