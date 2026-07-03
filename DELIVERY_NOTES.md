# Delivery Notes — Version 1.1.0

This release implements the requested corrections as follows:

| Request | Implementation |
|---|---|
| Per-school landing slider | School-owned slides with images, captions, ordering, enable/disable controls and a school-level slider switch. |
| Useful school dashboard | Current session/term, lock status, students, classes, subjects, score completion, publication counts and class-performance charts. |
| Users missing from school member form | Unassigned platform users are selectable and searchable; a new user may also be created inline. Multi-school grants remain platform-controlled. |
| Term not saved | Every tenant-owned create page explicitly injects the current school and validates related records before save. |
| Fresh data and bulk students | Legacy migration was removed. CSV template, create/update import, row errors and student-list export were added. |
| Student and class downloads | Student register CSV, A3 class broadsheet PDF and combined A4 report-card PDF were added. |
| Platform-managed school access | The platform user form grants role-based access per school; active membership is enforced by panels, routes and APIs. |
| Update-flow review | Compilation locks the term, snapshots are immutable, release replacement is controlled, API writes use optimistic locking and mobile conflicts remain recoverable. |

## Before production

Run the connected staging checks in `docs/VALIDATION_REPORT.md`, then complete `docs/UAT_CHECKLIST.md`. Configure storage, queues, mail, database backups, HTTPS and the optional Gemini key before accepting live results.
