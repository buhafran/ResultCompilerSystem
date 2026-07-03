# Architecture

## 1. Design goals

The replacement is designed around five non-negotiable properties:

1. **Tenant isolation:** every school-owned record carries `school_id`, every school request resolves an authorized tenant, and database uniqueness constraints include the school boundary.
2. **Assignment-based authorization:** teachers never choose arbitrary school/class/subject identifiers. The API accepts a server-created `teacher_assignment` and verifies the authenticated teacher against it.
3. **Reproducible results:** score entry is live data, while a compiled result is an immutable versioned snapshot. Releasing a result never depends on later edits to live scores.
4. **Reliable low-connectivity entry:** mobile changes are stored in SQLite before synchronization. Optimistic `lock_version` checks prevent silent overwrites.
5. **Auditable decisions:** score changes, compilation, release, and AI comment generation are recorded in `audit_logs`.

## 2. Components

```text
Teacher Mobile App
  ├─ SecureStore: bearer token and selected school
  ├─ SQLite: cached rosters and pending score changes
  └─ HTTPS JSON API
          │
          ▼
Laravel Application
  ├─ Sanctum authentication
  ├─ Assignment authorization
  ├─ ScoreEntryService
  ├─ ResultCompilerService
  ├─ AiCommentService
  ├─ Student portal and verification routes
  └─ Filament panels
       ├─ /platform: platform super administrators
       └─ /admin/school/{slug}: school tenant
          │
          ▼
MySQL 8.4
  ├─ school/user/academic master data
  ├─ result_entries (editable)
  ├─ result_publications (versioned release)
  ├─ result_summaries.snapshot (immutable report payload)
  └─ audit_logs
```

## 3. Roles

| Role | Main permissions |
|---|---|
| Platform administrator | Create/disable schools and global users; access every tenant through explicit membership/super-admin authority. |
| School administrator | Manage school profile, school users, classes, subjects, students, assignments, templates, compilation and release. |
| Examination officer | Manage academic/result operations and release results. |
| Teacher | Use only assigned score sheets. The web score register is limited to that teacher’s records. |
| Student/parent | Authenticate with school-specific admission number and PIN; view only released snapshots belonging to that student. |

## 4. Core data model

- `schools`: tenant identity, landing-page details and JSON policy settings.
- `school_user`: tenant membership and one role per user/school.
- `academic_sessions`, `academic_terms`: school-owned academic calendar; a locked term rejects edits.
- `school_classes`, `subjects`, `class_subjects`: school curriculum structure.
- `students`: school-specific admission number and hashed portal PIN.
- `teacher_assignments`: `(school, teacher, term, class, subject)` authorization tuple.
- `result_entries`: CA, examination, total, grade, status, subject position and edit version.
- `result_templates`: a vetted layout plus safe JSON branding options; schools cannot execute arbitrary Blade/PHP.
- `result_publications`: one class/term compilation version and release status.
- `result_summaries`: student-level total, average, position, comments, public token and complete JSON snapshot.
- `audit_logs`: actor, tenant, event, before/after state, IP and user agent.

## 5. Compilation algorithm

1. Lock the selected school class inside a database transaction.
2. Resolve all active students and all subjects assigned to the class.
3. Create missing result-entry sheets with `not_entered` status.
4. Stop compilation if any sheet is incomplete. Absence must be selected explicitly.
5. Recalculate present totals as `CA + Examination` and apply the school’s grade scale.
6. Rank each subject with standard competition ranking: `1, 2, 2, 4`.
7. Calculate each student’s total and average. The school setting decides whether absence counts as zero in the denominator.
8. Rank class averages with the same tie-safe competition ranking.
9. Create a new publication version and a full student snapshot containing school identity, student identity, academic context, subject rows, summary and grading scale.
10. Hash the ordered snapshots and save the checksum.
11. Keep the publication in `compiled` state until an authorized officer reviews and releases it.

## 6. Result lifecycle

```text
Score entry → Compile version → Review comments/template → Release
                   │                                      │
                   └─ immutable snapshots                 └─ visible in student portal
```

A new compilation after corrections creates version 2; it does not mutate version 1. When a newer version is released for the same class and term, the previously released version is automatically marked `withdrawn`; its immutable snapshot remains available to authorized administrators and audit history.

## 7. AI comments

`AiCommentService` sends anonymous score/grade patterns only—never name, admission number, gender, date of birth, school, class or address. Gemini is instructed not to infer sensitive characteristics or use shaming language. Structured JSON is required. If the API is absent or unavailable, a deterministic performance-band comment is returned. All AI output remains an editable draft and is audited.
