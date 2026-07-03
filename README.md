# Result System

**Release:** 1.1.0 — see [`CHANGELOG.md`](CHANGELOG.md) for the correction history and [`docs/VALIDATION_REPORT.md`](docs/VALIDATION_REPORT.md) for validation scope.

A production-oriented **multitenant school result compilation platform** built with Laravel 13, Filament 5, and an Expo teacher mobile application.

## Included modules

- Separate platform super-administration and tenant-aware school administration panels.
- Explicit school access grants for every user, with school-level roles and enforced tenant filtering.
- Configurable school landing page with enable/disable controls, image slides, ordering, call-to-action buttons, and responsive presentation.
- School dashboard showing the current session/term, active students, classes, subjects, score-entry completion, publication status, and latest class averages.
- Sessions, terms, classes, subjects, class-subject mappings, teachers, assignments, students, result templates, comments, compilation, review, and release.
- Individual student entry plus CSV template download, bulk CSV upload/update, and student-list export.
- CA 30% and Examination 70% entry with absence handling, optimistic locking, assignment authorization, and offline mobile synchronization.
- Transactional result compilation, tie-safe ranking, immutable publication snapshots, release history, and audit logs.
- Per-student A4 report cards, combined class report-card PDF, and A3 class broadsheet containing every subject.
- PIN-protected student portal and public result verification.
- Gemini-assisted comment drafts using anonymous performance information, with a deterministic fallback.
- Docker deployment, security guidance, API documentation, automated tests, and UAT checklists.

## Project layout

```text
ResultSystem/
├── backend/       Laravel, Filament, APIs, landing pages, student portal and PDF reports
├── mobile/        Expo/React Native offline-capable teacher application
├── deploy/        Docker Compose, PHP-FPM and Nginx files
├── docs/          Architecture, API, security, deployment and validation guides
└── Makefile       Common deployment and validation commands
```

## Fast deployment

1. Read [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).
2. Copy `backend/.env.example` to `backend/.env` and configure the production database, mail, queue, storage and Gemini settings.
3. Run `make up` for Docker deployment, or install Composer and Node dependencies for a conventional deployment.
4. Create the first platform administrator with `php artisan platform:make-admin`.
5. Sign in at `/platform`, create a school, and grant school access to administrators and teachers.
6. Open the school panel at `/admin/school/{school-slug}` and enter fresh academic data or bulk-upload students.

## Important URLs

- Platform administration: `/platform`
- School administration: `/admin` or `/admin/school/{school-slug}`
- Public school page: `/schools/{school-slug}`
- Student portal: `/schools/{school-slug}/portal`
- Teacher API: `/api/v1`

Do not commit `.env`, exported PIN files, uploaded student photographs, or production database backups.
