# Success Stories (user-submitted, admin-approved testimonials)

The homepage testimonial and the `/reviews` grid are **dynamic**: a signed-in
member shares their story from inside the app, a site administrator approves it,
and approved stories appear on the public site. Until at least one story is
approved, both pages fall back to clearly-labelled **illustrative sample
content** so they never look empty.

```
 user (active subscription)              site administrator
 ─────────────────────────               ────────────────────
 /stories  ──►  submit  ──►  pending  ──►  /admin/stories  ──►  approve
                                          (email = STORIES_ADMIN_EMAIL)  │
                                                                         ▼
                                                             approved  ──►  featured?  ──►  homepage spotlight
                                                                          └──────────────►  /reviews grid
```

## Why these rows are NOT tenant-scoped

Every other writeable model in Best CPMS carries the `BelongsToCompany` trait so a
user from Company A can never read Company B's data. Success stories are the
deliberate exception: an approved story is a **public marketing asset** shown on
a guest-accessible page, exactly like `contact_messages`. They therefore have a
**nullable `company_id`** (kept only for context/audit) and no company scope.

Isolation is preserved elsewhere:

- the **public read path** only ever selects `status = approved` rows;
- **writes are authorized on the server**, never the browser — `status`,
  `is_featured`, `company_id` and `submitted_by` are stamped by the controller
  from the authenticated user, never from form input;
- **moderation** is gated by the `story-admin` middleware, which matches the
  signed-in user's email against `config('stories.admin_email')`. The app has no
  platform-level super-admin role (every `User` role is company-scoped), so an
  email allow-list is the correct gate for a platform-wide screen.

## Data model

**Migration:** `database/migrations/2026_10_08_100000_create_success_stories_table.php`

`success_stories` columns: `id`, `name`, `role` (nullable), `organisation`
(nullable), `email` (nullable), `story` (text), `rating` (nullable 1–5),
`status` (enum `pending|approved|rejected`, default `pending`, indexed),
`is_featured` (bool, default false), `published_at` (nullable), `company_id`
(nullable FK → `companies`, `nullOnDelete`), `submitted_by` (nullable FK →
`users`, `nullOnDelete`), timestamps.

**Model:** `App\Models\SuccessStory`

- Constants `STATUS_PENDING` / `STATUS_APPROVED` / `STATUS_REJECTED` and a
  `STATUSES` array (used to validate the moderation filter and rejection logic).
- Scopes `approved()`, `pending()`, `featured()`.
- Helpers `isApproved()`, `isPending()`, `initials()` (first letters of the first
  two name words, uppercased, falling back to `C`), and `toPublicArray()` which
  shapes a row into `['name','org','stars','text','initials']` — the same keys
  the public views already used for sample content, where `org` is
  `"role, organisation"` (or just whichever is present).

## Config

`config/stories.php` (env-backed, scalar-only — safe to `config:cache`):

| Key | Env | Default | Purpose |
|-----|-----|---------|---------|
| `stories.admin_email` | `STORIES_ADMIN_EMAIL` | (none) | The one email allowed to moderate. Blank locks the screen to everyone. |
| `stories.home_limit` | — | `1` | How many approved stories the homepage spotlight shows. |

Set `STORIES_ADMIN_EMAIL` in `.env` to open the moderation screen; leave it
blank and no one can reach `/admin/stories`.

## Write path (member submits)

- **Routes** (inside the `subscription` gate, so only active subscribers submit):

  | Method | URL | Name | Action |
  |--------|-----|------|--------|
  | GET | `/stories` | `stories.index` | `StoryController@index` |
  | POST | `/stories` | `stories.store` | `StoryController@store` |

- **Request:** `App\Http\Requests\StoreSuccessStoryRequest`. `authorize()` is
  `(bool) $this->user()?->isActive()`; rules: `name` required/max 120, `role`
  nullable/max 120, `organisation` nullable/max 150, `email` nullable/email/max
  190, `rating` nullable integer 1–5, `story` required min 30/max 2000, and a
  `website` **honeypot** validated `prohibited` (same pattern as the contact forms).
- **Controller:** `StoryController@store` sets `status = pending`,
  `is_featured = false`, `company_id = $user->company_id` and
  `submitted_by = $user->id` **server-side**, then redirects back with a flash.
  `index()` lists the authed user's own submissions (their status only).
- **View:** `resources/views/stories/index.blade.php` — "Share your success
  story" form (name/email/organisation prefilled from the auth user) plus a list
  of the member's past submissions badged *Published* / *Not published* /
  *Awaiting review*.

## Moderation path (admin approves)

- **Middleware:** `App\Http\Middleware\EnsureStoryAdmin` (alias `story-admin`,
  registered in `bootstrap/app.php`). `abort(403)` unless a user is signed in
  **and** `strcasecmp(trim($user->email), config('stories.admin_email')) === 0`.
  A blank configured email locks the screen to everyone.
- **Routes** (inside `auth`, **outside** the `subscription` gate so moderation
  still works even if the admin's own company trial has lapsed):

  | Method | URL | Name | Action |
  |--------|-----|------|--------|
  | GET | `/admin/stories` | `admin.stories.index` | list, filtered by `?status=` |
  | PATCH | `/admin/stories/{story}/approve` | `admin.stories.approve` | approve |
  | PATCH | `/admin/stories/{story}/reject` | `admin.stories.reject` | reject |
  | PATCH | `/admin/stories/{story}/feature` | `admin.stories.feature` | toggle homepage feature |
  | DELETE | `/admin/stories/{story}` | `admin.stories.destroy` | delete |

- **Controller:** `App\Http\Controllers\AdminStoryController`. `index()`
  validates `?status` against `STATUSES` (+ `all`, default `pending`), paginates
  20 with `company`/`submitter` eager-loaded and `withQueryString()`, and computes
  per-status counts for the tabs. `approve()` sets `approved` and stamps
  `published_at` if unset. `reject()` sets `rejected` and clears `is_featured`.
  `feature()` only applies to approved rows and **clears the other featured rows
  first** so exactly one story is the homepage spotlight. `destroy()` deletes.
- **View:** `resources/views/admin/stories/index.blade.php` — status-filter tabs
  with counts, an empty state, and a card per story with the initials avatar,
  rating, feature/status badges, the quote, submit metadata, and approve /
  feature / reject / delete actions.

## Read path (public display)

`App\Support\Stories` is the only reader of approved rows. Every method is
wrapped in `try/catch (Throwable) → empty/null` so the public pages still render
**before the migration has run** (mirrors the defensive style of
`App\Support\Currency` / `Plan::current()`):

- `approved(int $limit = 0): Collection` — `approved()` ordered by
  `is_featured` ↓, `published_at` ↓, `id` ↓, optionally limited, mapped through
  `toPublicArray()`.
- `spotlight(): ?array` — the single featured/most recent approved story.
- `averageRating(): ?float` — mean `rating` over approved rows (non-null),
  rounded to 1 dp; `null` when there are none.
- `count(): int` — number of approved stories.

`PageController@home` passes `['story' => Stories::spotlight()]`;
`PageController@reviews` passes `stories`, `average`, and `storyCount`.

**Views fall back to sample content when nothing is approved:**

- `resources/views/public/home.blade.php` — builds `$spotlight` from `$story`
  or the labelled Meera Kulkarni sample, and renders `text` / `initials` /
  `name` / `org`.
- `resources/views/public/reviews.blade.php` — uses `$stories` when non-empty,
  else the six sample reviews; the rating summary shows the **live average** when
  available, and the "illustrative samples" footnote shows only when there is no
  live content.

## Navigation

`resources/views/layouts/app.blade.php` builds `$navItems`:

- **Share your story** — shown to every active member (next to *Planning*).
- **Stories** — shown only when the signed-in user's email matches
  `config('stories.admin_email')`; links to `admin.stories.index`.

## Setup / operations

1. Run `php artisan migrate` to create the `success_stories` table.
2. Set `STORIES_ADMIN_EMAIL` in `.env` to the moderator's email address.
3. Optionally `php artisan config:clear` (or `config:cache`) after changing it.

## Notes / follow-ups

- Moderation is a single hard-coded email; promote to a role/table only if more
  than one moderator is ever needed.
- No email notification is sent to the admin on a new submission (a possible
  follow-up, using the existing mail setup).
- Stories are shown verbatim after approval — consider adding light editing or
  an explicit consent line if submissions are ever collected from the public.
