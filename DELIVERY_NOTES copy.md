# Delivery Notes - Result System v1.2.0

This update adds bilingual subject subtitles, class-position visibility control, student photos on report sheets, QR-code verification, and an enhanced platform dashboard.

## Deployment

1. Pull or copy the updated source.
2. Run Composer because a QR-code dependency was added:
   `composer install --no-dev --prefer-dist --optimize-autoloader`
3. Run pending migrations:
   `php artisan migrate --force`
4. Clear and rebuild caches:
   `php artisan optimize:clear && php artisan optimize`
5. Rebuild Docker image if using Docker deployment.

## Important

The new report setting is located at:
School Admin → School Profile → Result sheet options → Show student class position.

The QR-code feature uses `bacon/bacon-qr-code` and the SVG renderer so it does not require Imagick.
