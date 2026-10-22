# Public Site (Marketing, Content & Legal Pages)

The public-facing website that sits in front of the authenticated Best CPMS app.
All pages are guest-accessible (no `auth`/`subscription` middleware) and share a
common layout. Signed-in users hitting `/` are redirected to their dashboard.

## Design system

- **Layout:** `resources/views/layouts/public.blade.php` — sticky marketing nav
  (adaptive: guests see *Log in* / *Start free trial*; authed users see *Go to
  dashboard*), a four-column footer, Google Fonts, and inline Tailwind config.
- **Hero partial:** `resources/views/partials/public-hero.blade.php` — reusable
  page header. Usage: `@include('partials.public-hero', ['kicker' => '…', 'title' => '…', 'lead' => '…'])`
  (`kicker` and `lead` optional).
- **Type:** Space Grotesk (display/headings, `font-display`) + IBM Plex Sans
  (body, `font-sans`), loaded via Google Fonts.
- **Palette:** slate-900 ink, white + slate-50 bands, indigo-600 brand (shared
  with the app), a single amber-500 hi-vis accent for primary CTAs.
- **Motif:** faint "blueprint" drafting grid (`.blueprint-grid`) behind heroes;
  hairline borders; left-aligned content. No build step — Tailwind via CDN.

## Routes → views (all in `routes/web.php`, above the guest group)

| URL | Route name | Controller | View |
|-----|-----------|------------|------|
| `/` | `home` | `PageController@home` | `public/home` (redirects authed → dashboard) |
| `/about` | `about` | `PageController@about` | `public/about` |
| `/mission-and-vision` | `mission` | `PageController@mission` | `public/mission` |
| `/team` | `team` | `PageController@team` | `public/team` |
| `/careers` | `careers` | `PageController@careers` | `public/careers` |
| `/features` | `features` | `PageController@features` | `public/features` |
| `/pricing` | `pricing` | `PageController@pricing` | `public/pricing` (reads `config/billing`) |
| `/reviews` | `reviews` | `PageController@reviews` | `public/reviews` |
| `/services/planning-scheduling` | `services.planning` | `PageController@servicePlanning` | `public/services/planning` |
| `/services/cost-control` | `services.cost` | `PageController@serviceCost` | `public/services/cost` |
| `/services/field-tracking` | `services.field` | `PageController@serviceField` | `public/services/field` |
| `/blog` | `blog` | `PageController@blog` | `public/blog` |
| `/case-studies` | `case-studies` | `PageController@caseStudies` | `public/case-studies` |
| `/faq` | `faq` | `PageController@faq` | `public/faq` |
| `/help` | `help` | `PageController@help` | `public/help` |
| `/privacy-policy` | `privacy` | `PageController@privacy` | `public/legal/privacy` |
| `/terms-and-conditions` | `terms` | `PageController@terms` | `public/legal/terms` |
| `/refund-policy` | `refund` | `PageController@refund` | `public/legal/refund` |
| `/cookie-policy` | `cookies` | `PageController@cookies` | `public/legal/cookies` |
| `/contact` (GET/POST) | `contact` / `contact.store` | `ContactController` | `public/contact` |
| `/feedback` (GET/POST) | `feedback` / `feedback.store` | `ContactController` | `public/feedback` |

The two POST routes are rate-limited with `throttle:10,1`.

## Dynamic testimonials (success stories)

The homepage testimonial and the `/reviews` grid are **user-submitted and
admin-approved**, not hard-coded. A signed-in member posts their story at
`/stories`; a site administrator approves it at `/admin/stories`; approved
stories feed the homepage spotlight (`PageController@home`) and the reviews grid
(`PageController@reviews`) via `App\Support\Stories`. Both pages fall back to the
labelled sample content below until a story is approved. Full detail (data model,
routes, moderation gate, setup) lives in **`docs/SUCCESS-STORIES.md`**.

## Footer structure (as specified)

- **Company:** About Us · Our Team · Careers · Contact Us
- **Services:** Planning & Scheduling · Cost & Budget Control · Field & Progress Tracking
- **Resources:** Blog · FAQ · Case Studies
- **Legal:** Privacy Policy · Terms & Conditions · Refund Policy
- **Bottom bar:** Cookie Policy · Help Center · Mission & Vision · Give Feedback · © line
- **Social icons:** LinkedIn · X · Facebook · Instagram (under the brand blurb),
  driven by `config/social.php` (env `SOCIAL_*_URL`). See `docs/SOCIAL-LOGIN.md`.

## Contact & feedback backend

One table serves both forms, distinguished by a `type` enum.

- **Migration:** `database/migrations/2026_09_29_100000_create_contact_messages_table.php`
  — `contact_messages` (`type` enum `contact|feedback`, name, email, phone, company,
  subject, message, `rating`, `ip_address`, timestamps). **Deliberately NOT
  tenant-scoped** — submitters are anonymous public visitors, so no `company_id`.
- **Model:** `App\Models\ContactMessage` (no `BelongsToCompany` trait). Consts
  `TYPE_CONTACT` / `TYPE_FEEDBACK`; `isFeedback()` helper.
- **Validation:** `StoreContactRequest`, `StoreFeedbackRequest`. Both include a
  `website` **honeypot** field validated as `prohibited` (must be empty/absent).
- **Controller:** `ContactController` saves the row (stamping `type` + `ip_address`),
  then emails it. The DB write is authoritative; mail is wrapped in try/catch and
  logged on failure, so a missing/broken SMTP config never breaks the form.
- **Mail:** `App\Mail\ContactMessageReceived` → `resources/views/emails/contact-message.blade.php`.
  Sent to `config('mail.contact_to')`, which defaults to **abc@gmail.com**
  (override via `CONTACT_TO` in `.env`). `replyTo` is set to the submitter.
- **Dev note:** `MAIL_MAILER` defaults to `log`, so in local dev the notification
  is written to the Laravel log rather than actually sent.

## Notes / follow-ups

- Team members, case studies, and blog posts use clearly-labelled
  **illustrative sample content** — replace with real content before launch.
  (Reviews are now dynamic — see *Dynamic testimonials* above — and fall back to
  sample content only until a real story is approved.)
- Legal pages carry a "review with counsel" note; they are standard templates,
  not vetted legal text.
- Run `php artisan migrate` to create the `contact_messages` and
  `success_stories` tables.
