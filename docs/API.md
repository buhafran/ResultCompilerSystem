# Teacher Mobile API

Base path: `/api/v1`

All protected calls use:

```http
Authorization: Bearer {sanctum-token}
Accept: application/json
Content-Type: application/json
```

## Authentication

### `POST /login`

```json
{
  "email": "teacher@example.edu",
  "password": "secret",
  "device_name": "Teacher Android Phone"
}
```

Returns a 30-day token plus the user’s active schools and role in each school.

### `GET /me`

Returns the authenticated user and active memberships.

### `POST /logout`

Deletes the current device token.

## Assignments

### `GET /schools/{school-slug}/assignments`

Returns only the authenticated teacher’s assignments; school managers can see all. Each item includes term, class, subject, roster size and completion count.

### `GET /schools/{school-slug}/assignments/{assignment}/roster`

Returns active students and current scores. The server verifies the assignment belongs to the route school and authenticated teacher.

## Save a score

### `PUT /schools/{school-slug}/assignments/{assignment}/score`

```json
{
  "student_id": 410,
  "ca_score": 26,
  "exam_score": 61,
  "status": "present",
  "lock_version": 3
}
```

Absent student:

```json
{
  "student_id": 411,
  "ca_score": null,
  "exam_score": null,
  "status": "absent",
  "lock_version": 1
}
```

Validation enforces the school-configured maximums, currently 30 and 70 by default. A stale `lock_version` returns a conflict-style validation error rather than overwriting newer data.

## Offline synchronization

### `POST /schools/{school-slug}/assignments/{assignment}/sync`

```json
{
  "changes": [
    {
      "client_id": "device-generated-id",
      "student_id": 410,
      "ca_score": 26,
      "exam_score": 61,
      "status": "present",
      "lock_version": 3
    }
  ]
}
```

The API returns separate `saved` and `errors` arrays and may use HTTP `207` for a partially successful batch. The app removes only confirmed client IDs from SQLite.

## AI comment draft

### `POST /schools/{school-slug}/result-summaries/{summary}/ai-comments`

School manager only. Generates anonymous AI drafts, stores them for review, and writes an audit event.
