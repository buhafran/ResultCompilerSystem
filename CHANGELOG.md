# Changelog
## v1.2.5 - Livewire Upload 401 Fix

- Added Laravel trusted proxy configuration for Apache/Nginx HTTPS deployments.
- Added Livewire temporary upload configuration for local private temporary storage and 30MB upload validation.
- Added production upload troubleshooting documentation for 401 errors during file attachment.
- Updated environment example with production session and Livewire upload settings.

## 1.2.2 - 2026-07-12

### Added
- Bulk CSV upload for school classes, including a template populated with common Nigerian class names.
- Bulk CSV upload for subjects, including a template populated with common subject names and codes.
- Multiple-subject selection when creating class subjects.
- Multiple-subject selection when creating teacher assignments.
- Bulk selection checkboxes for generating AI result comments for multiple students.

### Changed
- Updated school website navigation-group property typing to `string | \UnitEnum | null` for Filament compatibility.
- Registered the API rate limiter in `AppServiceProvider` using `RateLimiter::for('api', ...)`.
- Replaced unsupported `orWhereKey()` calls in school membership user selection with `orWhere('id', ...)`.
- Reapplied the Docker production build fix so Composer runs with required PHP extensions such as `intl`.

## 1.2.0 - 2026-07-11

### Added
- Optional subject subtitle / translation field shown beneath subject names on report sheets.
- School profile setting to show or hide student class position on report sheets.
- Student photo display on individual and combined report-card PDFs when a student photo exists.
- QR-code based result verification on report sheets when verification is enabled.
- Platform dashboard widgets for active schools, active students, school users, released results, active students by school, and publication status.
- Active student, active class, and active subject summary columns on the platform school list.

### Changed
- Result compilation snapshots now include subject subtitle, student photo path, and school report-display flags.
- Verification links on report sheets are no longer printed as plain URLs; they are rendered as QR codes with a short verification code.

### Database
- Added nullable `subtitle` column to `subjects`.

## v1.2.4 - File upload runtime fix

- Added production PHP upload limits for Docker (`upload_max_filesize=25M`, `post_max_size=30M`).
- Increased Docker Nginx upload body limit to 30M.
- Ensured Livewire temporary upload and import directories are created at container startup.
- Ensured public upload directories for logos, slides, signatures, and student photos are created at startup.
- Kept Docker build fixes from v1.2.3.
