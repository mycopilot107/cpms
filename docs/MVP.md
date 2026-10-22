# Best CPMS — MVP (Phases 2–6)

Construction Project Management System: a multi-tenant Laravel SaaS. This
document covers everything built after Phase 1 (Foundation) — authentication,
tenant isolation, dashboard, and the Projects / Tasks / Team modules — and how
to run and verify it.

---

## 1. What's in the MVP

| Phase | Feature | Status |
|-------|---------|--------|
| 2 | Company registration (transactional: company + owner + trial) | ✅ |
| 2 | Login / logout with throttling + disabled-account block | ✅ |
| 3 | Multi-tenant isolation (global scope + guarded keys + policies) | ✅ |
| 3 | Subscription/trial gate middleware | ✅ |
| 4 | Dashboard with live trial countdown + stats | ✅ |
| 5 | Team (users) CRUD with roles | ✅ |
| 6 | Projects CRUD | ✅ |
| 6 | Tasks CRUD with assignment, status, priority, progress, dependency | ✅ |
| + | Finance — BOQ, expenses, project payments, budget-vs-spend | ✅ |
| + | Planning — month calendar, CSS Gantt, milestones | ✅ |
| + | Reports — project / task / cost / progress, each with CSV export | ✅ |

The Finance, Planning, and Reports modules are documented in full in
`docs/MODULES.md`.

UI is server-rendered Blade styled with Tailwind (via CDN — no Node build step
required).

---

## 2. Running it locally

The app cannot be run inside the build sandbox (no PHP/Composer/DB there), so run
these on your own machine from the project root:

```sh
# 1. Install dependencies (first time only)
composer install

# 2. Environment
cp .env.example .env        # if you don't already have .env
php artisan key:generate    # if APP_KEY is empty

# 3. Configure the database in .env, e.g.
#    DB_CONNECTION=mysql
#    DB_DATABASE=lr_test
#    DB_USERNAME=pmauser
#    DB_PASSWORD=********

# 4. Build the schema and demo data
php artisan migrate:fresh --seed

# 5. Serve
php artisan serve
# visit http://127.0.0.1:8000  → redirects to /login
```

### Demo logins (all password: `password`)

| Email | Role |
|-------|------|
| owner@demo.test | owner |
| manager@demo.test | manager |
| engineer@demo.test | engineer |
| supervisor@demo.test | supervisor |
| worker@demo.test | worker |

The seed creates one company ("ABC Construction") with a 15-day trial, three
projects, and several tasks assigned across the team. Registering a new account
at `/register` creates a *separate* tenant — a good way to prove isolation.

---

## 3. Security model (tenant isolation)

The spec requirement — *"a user from Company A must NEVER access Company B's
data"* — is enforced in depth, at four independent layers:

1. **Automatic global scope.** `App\Models\Concerns\BelongsToCompany` (used by
   `Project` and `Task`) adds a global query scope that appends
   `where company_id = <current user's company>` to *every* query while a user
   is logged in. Even a forgotten `where()` cannot leak another tenant's rows.
   Route-model binding therefore returns **404** for a foreign id.

2. **Guarded ownership keys.** `company_id`, `role`, and `status` (users) and
   `company_id` / `created_by` (projects, tasks) are **not** in `$fillable`.
   They are set server-side only, so a crafted `company_id`/`role` field in a
   POST body is silently ignored — no privilege escalation or tenant hopping.

3. **Authorization policies.** `UserPolicy`, `ProjectPolicy`, and `TaskPolicy`
   re-check `company_id` on every action (`view`/`update`/`delete`) and apply
   role rules. There is deliberately **no `before()` owner-superuser hook**, so
   an owner still cannot reach across tenants.

4. **Scoped validation.** Foreign keys accepted from forms (`project_id`,
   `assigned_to`, `dependency_id`) are validated with
   `Rule::exists(...)->where('company_id', $companyId)`, so a cross-tenant id
   fails validation before it ever reaches the database.

> **Why the `User` model has no global scope:** the auth model is queried during
> login/session resolution *before* a user is established. A tenant global scope
> keyed on `Auth::user()` there risks recursion, so `User` queries are scoped
> explicitly by `company_id` in the controllers instead.

**Trial/subscription gate.** The `subscription` middleware lets a company in only
while it is on trial or has an active subscription; otherwise it redirects to
`/subscription/expired` (data is never deleted). `logout` and the expired page
sit *outside* that gate to avoid a redirect loop.

---

## 4. Role → permission matrix

| Action | owner | admin | manager | supervisor | engineer | worker | accountant |
|--------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| View projects/tasks | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Create/edit projects | ✅ | ✅ | ✅ | — | — | — | — |
| Delete projects | ✅ | ✅ | — | — | — | — | — |
| Create/edit any task | ✅ | ✅ | ✅ | ✅ | — | — | — |
| Update *assigned* task | ✅ | ✅ | ✅ | ✅ | ✅ (if assignee) | ✅ (if assignee) | ✅ (if assignee) |
| Delete tasks | ✅ | ✅ | ✅ | ✅ | — | — | — |
| Manage team (users) | ✅ | ✅ | — | — | — | — | — |

The single **owner** is created at registration; that role cannot be reassigned
through the team form, and the owner account cannot be deleted or deactivated.

---

## 5. Route map

| Method | URI | Name | Notes |
|--------|-----|------|-------|
| GET | `/` | — | redirects to dashboard or login |
| GET/POST | `/register` | `register`, `register.store` | guest only |
| GET/POST | `/login` | `login`, `login.store` | guest only, throttled |
| POST | `/logout` | `logout` | auth |
| GET | `/subscription/expired` | `subscription.expired` | auth (outside gate) |
| GET | `/dashboard` | `dashboard` | auth + subscription |
| — | `/projects` | `projects.*` | resource (full) |
| — | `/tasks` | `tasks.*` | resource (full) |
| — | `/users` | `users.*` | resource (no `show`) |
| GET | `/planning/calendar` | `planning.calendar` | all active users |
| GET | `/planning/gantt` | `planning.gantt` | all active users |
| — | `/milestones` | `milestones.*` | resource (no `show`); planning roles |
| GET | `/finance` | `finance.index` | finance roles |
| — | `/boq-items` | `boq-items.*` | resource (no `show`), param `boqItem` |
| — | `/expenses` | `expenses.*` | resource (no `show`) |
| — | `/project-payments` | `project-payments.*` | resource (no `show`), param `projectPayment` |
| GET | `/reports` | `reports.index` | finance roles |
| GET | `/reports/{projects,tasks,cost,progress}` | `reports.*` | finance roles; `?export=csv` |

---

## 6. Key files

```
app/
  Models/                 Company, User, Subscription, Project, Task
  Models/Concerns/        BelongsToCompany.php        (tenant global scope + stamp)
  Http/Middleware/        EnsureSubscriptionActive.php
  Http/Controllers/       Dashboard, Project, Task, User + Auth/*
  Http/Requests/          Store/Update Project|Task|User, Auth/Register|Login
  Policies/               User, Project, Task
database/
  migrations/             companies, subscriptions, users-alter, projects, tasks
  factories/              Company, User, Project, Task
  seeders/DatabaseSeeder.php
resources/views/
  layouts/                app (authed shell), guest
  auth/                   login, register
  dashboard.blade.php
  projects/  tasks/  users/   index/create/edit/show(+ _form partials)
  partials/               status & priority badges
  subscription/expired.blade.php
routes/web.php
bootstrap/app.php          (middleware alias + guest→login redirect)
```

---

## 7. Verification performed

Because PHP can't run in the build environment, the MVP was verified statically:
bracket balance across all PHP files, matched Blade directives, and a
cross-check that every `route()` / `view()` / `@include()` reference resolves to
a defined route or file. Migration order was confirmed to satisfy all
foreign-key dependencies. **Run `php artisan migrate:fresh --seed` and click
through the app to confirm at runtime.**

Suggested manual smoke test for isolation: register a second company, create a
project in each, then confirm neither company can see or open (`/projects/{id}`)
the other's project — the second should 404.

---

## 8. Not in scope (future phases)

Email verification & password reset, file uploads (logos, attachments),
and notifications. The remaining sidebar modules from the product brief —
**Materials, Workforce, and Settings** — are not yet built.

### Added since the MVP

- **Responsive UI** — every screen works on mobile/tablet/desktop (see the
  responsive note in the project memory and `docs/responsive-preview.html`).
- **Mobile API + Flutter app** — a JSON API (`/api`, bearer-token auth, no
  Sanctum dependency) and a Flutter field app in `/mobile` let workers view
  their assigned tasks and report status/progress/hours. Tenancy and policies
  are shared with the web app. See `docs/API.md` and `docs/MOBILE.md`.
- **Subscription billing (Razorpay)** — after the trial, companies buy a paid
  plan (₹1000/user/month, 1/3/6/12 months, min 2 users) through Razorpay Checkout.
  Prices are editable in the `plans` / `plan_prices` tables.
  Server-side price computation, HMAC signature verification, a `payments`
  ledger, and a hard seat cap. No Composer package required. See
  `docs/BILLING.md`.
- **Finance, Planning & Reports** — BOQ/expenses/payments with budget-vs-spend
  roll-ups; a month calendar, CSS-only Gantt, and milestones; and four reports
  (project, task, cost, progress) each exportable to CSV. All tenant-scoped and
  role-gated, no new Composer packages. See `docs/MODULES.md`.
