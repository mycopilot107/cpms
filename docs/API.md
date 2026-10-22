# Best CPMS Mobile API

A small JSON API that backs the Flutter field app (`/mobile`). It reuses the
web app's models, tenancy, and policies — nothing about isolation or
authorization is re-implemented here, it simply rides on the same machinery.

- **Base path:** `/api`
- **Format:** JSON in, JSON out. Send `Accept: application/json`.
- **Auth:** bearer token — `Authorization: Bearer <token>`.

---

## 1. Authentication model

There is **no Sanctum/Passport dependency**. Auth is a self-contained custom
guard:

- `api_tokens` table stores a **SHA-256 hash** of each token (never the
  plaintext), a device label, and `last_used_at`. Tokens cascade-delete with
  their user.
- The `api` guard (config/auth.php) uses a custom `api-token` request driver
  registered in `AppServiceProvider::boot()`. It hashes the presented bearer
  token, looks it up, and returns the owning user **only if the account is
  active**.
- Because the driver returns a real `User`, `Auth::check()` becomes true and
  the **`BelongsToCompany` global scope and all model policies apply to API
  requests automatically** — exactly as on the web. Route-model binding for
  `{task}` therefore 404s any id outside the caller's company.

Routes live in `routes/api.php`; everything except `POST /login` is behind
`auth:api`.

> The subscription/trial gate is **not** applied to the API (it issues an HTML
> redirect meant for browsers). Billing state remains a web concern for now;
> tenant isolation is unaffected.

---

## 2. Endpoints

### POST /api/login
Public. Exchanges credentials for a token. Throttled by email+IP (5 attempts),
and rejects disabled accounts even with a correct password. The error message
is identical for "no such user" and "wrong password".

Request:
```json
{ "email": "worker@demo.test", "password": "password", "device_name": "Best CPMS mobile" }
```
`device_name` is optional (defaults to `mobile`).

`200 OK`:
```json
{
  "token": "raw-token-shown-only-once",
  "user": {
    "id": 5, "name": "Demo Worker", "email": "worker@demo.test",
    "phone": null, "role": "worker", "status": "active",
    "company": { "id": 1, "name": "ABC Construction" }
  }
}
```
`422` on bad credentials / disabled account / throttle, with:
```json
{ "message": "...", "errors": { "email": ["These credentials do not match our records."] } }
```

### GET /api/me
Auth required. The current user, wrapped in a `data` envelope (default resource
behavior):
```json
{ "data": { "id": 5, "name": "...", "role": "worker", "company": { "id": 1, "name": "..." }, ... } }
```

### POST /api/logout
Auth required. Revokes (deletes) the token used for this request.
```json
{ "message": "Logged out." }
```

### GET /api/tasks?scope=open|all
Auth required. Returns **only tasks assigned to the caller**, within their
company. `scope=open` (default) hides `completed` and `cancelled`; `scope=all`
returns everything assigned. Ordered by due date (soonest first, undated last).

`200 OK`:
```json
{
  "data": [
    {
      "id": 12, "title": "Pour foundation", "description": "...",
      "status": "in_progress", "priority": "high", "progress": 40,
      "start_date": "2026-09-01", "due_date": "2026-09-30",
      "estimated_hours": 12.0, "actual_hours": 4.5, "is_overdue": false,
      "project": { "id": 3, "name": "Riverside Tower", "code": "RT-001" },
      "assignee": { "id": 5, "name": "Demo Worker" }
    }
  ]
}
```

### GET /api/tasks/{task}
Auth required. A single task in the caller's company (`view` policy — any
same-company task; cross-tenant ids 404). Same object shape as above, wrapped in
`data`.

### PATCH /api/tasks/{task}
Auth required. **Progress reporting only.** Authorized by `TaskPolicy::update`
— planners or the task's own assignee. Accepts any subset of:

```json
{ "status": "completed", "progress": 100, "actual_hours": 8 }
```

- `status` — one of `pending | in_progress | completed | cancelled`
- `progress` — integer 0–100
- `actual_hours` — number ≥ 0, or `null` to clear

Fields not listed here (project, assignee, title, dates, priority…) are **not
accepted** on this endpoint — a worker cannot reassign or reschedule work from
the app. Returns the updated task (wrapped in `data`).

---

## 3. Errors

| Status | Meaning                                              |
|--------|------------------------------------------------------|
| 401    | Missing/invalid/expired token → app drops session    |
| 403    | Authenticated but not allowed (e.g. updating a task that isn't yours) |
| 404    | Not found, **including cross-tenant ids** (by design)|
| 422    | Validation error — `{ "message", "errors": {field:[...]}}` |

---

## 4. Response envelopes (contract the Flutter models rely on)

- `POST /login` → **flat** `{ token, user }` (user object is not wrapped).
- `GET /me`, `GET /tasks`, `GET /tasks/{id}`, `PATCH /tasks/{id}` → wrapped in
  `{ "data": ... }` (Laravel `JsonResource` default).

The Flutter `ApiClient`/services unwrap `data` where present; `User.fromJson`
and `Task.fromJson` map these exact field names.

---

## 5. Not implemented yet

Photo/attachment upload, task comments, push notifications, and offline write
queueing. Each would be additive: a new `TaskController` action or resource plus
a matching Flutter service method — no change to the auth or tenancy design.
