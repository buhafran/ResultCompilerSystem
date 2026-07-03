# Changelog

## 1.1.0 — 3 July 2026

- Added a tenant-owned landing-page slider with per-school enablement, ordering, images, captions and call-to-action links.
- Added a school dashboard with current-term status, student/class/subject counts, accurate score-entry completion, publication status and class-average charts.
- Corrected school membership creation so unassigned users appear, new users can be created inline, and cross-school grants remain controlled by the platform super administrator.
- Corrected term creation by injecting and validating the current school on every tenant-owned create workflow.
- Removed old-database import functionality; the package now assumes fresh academic data entry.
- Added student CSV template download, bulk create/update, row-level error reporting and student-list CSV export.
- Added A3 class broadsheet PDF and one combined A4 report-card PDF for a complete class.
- Enforced active school memberships in Filament tenancy, downloads and the teacher API.
- Changed compilation to lock the term before taking immutable snapshots.
- Improved mobile conflict handling so unsynchronized edits remain visible and recoverable; locked terms are read-only.
- Updated custom actions to the Filament 5 `schema()` API and strengthened upload, URL and CSV-export safety.
