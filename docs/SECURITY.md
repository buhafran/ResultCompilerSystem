# Security and Privacy Controls

## Tenant controls

- School-owned tables contain `school_id` and tenant-aware unique/index constraints.
- Filament resolves the active school from the authenticated user’s membership.
- Server-side services repeat ownership checks; they do not rely on a hidden form field or client route alone.
- Platform super-admin access is isolated in `/platform`.

## Role controls

- School master-data resources are hidden and denied to teachers.
- A teacher’s score operation must match one `teacher_assignments` tuple.
- Compilation/release and AI comment actions require school administrator or examination officer authority.
- Student portals query by both school and authenticated student ID.

## Authentication and secrets

- Web passwords and portal PINs are hashed.
- Mobile tokens are stored with Expo SecureStore, not plain AsyncStorage.
- Sanctum tokens can be revoked per device.
- Gemini keys exist only on the server.
- Production must use HTTPS and strong application/database credentials.

## Result integrity

- Compilation uses a database transaction and record locks.
- Missing scores block compilation; absence must be explicit.
- Mobile edits use optimistic versions.
- Every publication has a version, timestamp, actor and SHA-256 checksum.
- Released reports read from a JSON snapshot, not mutable score rows.

## AI privacy

The AI service receives anonymous performance statistics. It excludes student name, admission number, gender, birth date, class, school and contact information. Output is a draft, remains editable, and is audited. Disable externally hosted AI with:

```dotenv
AI_COMMENTS_ENABLED=false
GEMINI_API_KEY=
```

The deterministic fallback continues to work.

## Operational requirements

- Encrypt production disks and backups.
- Restrict backup/PIN export access and record downloads.
- Rotate credentials after migration.
- Configure retention for audit and result data according to school policy and applicable Nigerian data-protection obligations.
- Add an application firewall/rate limiting at the reverse proxy for internet exposure.
- Test restore procedures, not only backup creation.
