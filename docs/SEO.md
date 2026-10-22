# SEO & Discoverability

Everything that makes the public Best CPMS site findable on Google for
**construction project management** searches: per-page metadata, structured
data, a keyword landing page, a dynamic sitemap/robots, and the brand's
favicon + social share image.

This layer is additive and presentational — it touches only the public
marketing views, one new config file, one controller, and three routes. No
database, no new Composer packages, no build step.

## What was added

| Area | What | Where |
|------|------|-------|
| **Config** | Central brand / org / default-metadata (env-backed, no secrets) | `config/seo.php` |
| **`<head>` metadata** | Per-page `<title>`, description, keywords, robots, canonical, Open Graph, Twitter card, favicons, theme-color | `resources/views/layouts/public.blade.php` |
| **Sitewide structured data** | `Organization` + `WebSite` JSON-LD | `layouts/public.blade.php` |
| **Per-page structured data** | `SoftwareApplication`, `Service`, `BreadcrumbList`, `FAQPage` via `@push('ld')` → `@stack('ld')` | home, pillar, service & FAQ views |
| **Keyword pillar page** | New landing page targeting the head term | `public/cpm-software.blade.php`, route `cpm-software` |
| **Solutions landing pages** | Data-driven keyword pages (by audience + use-case) that expand coverage beneath the pillar | `config/landing.php`, `LandingController`, `public/landing.blade.php` |
| **Dynamic sitemap + robots** | `/sitemap.xml` + `/robots.txt` generated from routes, always match `APP_URL` | `app/Http/Controllers/SitemapController.php` |
| **Brand assets** | `favicon.ico`, `favicon.svg`, `apple-touch-icon.png`, `og-image.png` (1200×630) | `public/` |

## Keyword architecture (pillar + cluster)

To avoid pages competing with each other for the same term
(*keyword cannibalization*), each page targets a distinct search intent:

| Page | URL | Primary keyword |
|------|-----|-----------------|
| **Pillar** | `/construction-project-management-software` | construction project management software / system *(head term)* |
| Home | `/` | brand-led (*"Construction project management, in one place"*) — intentionally **not** the exact head term, so it doesn't fight the pillar |
| Planning service | `/services/planning-scheduling` | construction scheduling software |
| Cost service | `/services/cost-control` | construction cost control / BOQ software |
| Field service | `/services/field-tracking` | construction site progress tracking |
| Features | `/features` | construction management software features |
| Pricing | `/pricing` | construction software pricing |
| FAQ | `/faq` | construction project management FAQ |

The pillar links down to the cluster (service, features, pricing) pages, and
the footer links up to the pillar sitewide with a keyword anchor
(*"Construction PM Software"*), concentrating relevance on the head term.

## Solutions landing pages (keyword cluster)

Beneath the pillar sits a cluster of **keyword landing pages** that each target
a distinct, longer-tail search intent — split into *audience* pages (who you
are) and *use-case* pages (what you need to do). They widen the set of queries
the site can rank for without competing with the pillar, which stays focused on
the head term.

| Key | URL | Primary keyword | Type |
|-----|-----|-----------------|------|
| `contractors` | `/construction-project-management-software-for-contractors` | construction project management software for contractors | audience |
| `builders` | `/construction-management-software-for-builders` | construction management software for builders | audience |
| `small-business` | `/construction-management-software-for-small-business` | construction management software for small business | audience |
| `developers` | `/project-management-software-for-real-estate-developers` | project management software for real estate developers | audience |
| `task-management` | `/construction-task-management-software` | construction task management software | use-case |
| `project-tracking` | `/construction-project-tracking-software` | construction project tracking software | use-case |
| `reporting` | `/construction-reporting-software` | construction reporting software | use-case |

**How it's built (one template, config-driven).** All seven pages are defined
as data in `config/landing.php` and rendered by a single Blade template
(`resources/views/public/landing.blade.php`) via `LandingController`. Adding a
new page is just another array entry — no new controller, view, or route by
hand.

- **Routes** are registered by a loop in `routes/web.php` using
  controller-action syntax (`[LandingController::class, 'show']`), **never
  closures**, so `php artisan route:cache` still works. Each gets the name
  `landing.{key}`.
- **Which page renders** is resolved by the controller from the *matched route
  name* (`landing.contractors` → key `contractors`), not from a URL segment or
  any browser-supplied value, and an unknown key 404s. Nothing to spoof.
- **Pricing stays in sync**: the config text uses tokens (`:symbol`,
  `:perUser`, `:minSeats`, `:fromMonth`, `:trialDays`) substituted in the
  template from `config/billing.php`, so `config/landing.php` holds no
  duplicated prices and remains cacheable.
- **Interlinking**: the pillar page lists all solutions ("Solutions for every
  team and workflow"), each landing page cross-links to its siblings and back
  up to the pillar, the footer carries a sitewide **Solutions** column, and all
  seven are in `sitemap.xml`.

**Avoiding thin / doorway pages.** Google penalises sets of near-duplicate
pages that exist only to catch keyword variants. Each landing page here has
genuinely distinct copy — its own intro, feature selection, emphasis and FAQs —
and every feature maps to a capability the product actually has. The config is
the guardrail: if a new entry can't be written with real, page-specific
substance, it shouldn't be added.


## Structured data (schema.org JSON-LD)

| Schema | Emitted on | Purpose |
|--------|-----------|---------|
| `Organization` | every public page (layout) | brand entity, logo, `sameAs` social profiles |
| `WebSite` | every public page (layout) | site entity |
| `SoftwareApplication` | home + pillar | product + an **array of `Offer`s, one per currency** (INR & USD) — prices from `config/billing` (see [MULTI-CURRENCY.md](MULTI-CURRENCY.md)) |
| `Service` + `BreadcrumbList` | each `/services/*` page + every Solutions landing page | service type + breadcrumb trail |
| `FAQPage` | pillar + `/faq` + every Solutions landing page | eligible for FAQ rich results |

**Deliberately NOT emitted** (honest-SEO guardrails — faking these risks a
Google manual penalty):

- **No `aggregateRating`/`Review` schema** — the on-site reviews are clearly
  labelled *illustrative sample content*. Don't emit rating schema until real,
  verifiable reviews exist.
- **No `SearchAction` / sitelinks searchbox** — the site has no search feature,
  so advertising one would be false.

`sameAs` only lists social profiles that are actually configured (the layout
filters out the platform-home fallbacks from `config/social.php`), so we never
claim a profile that doesn't exist.

## Configuration

Everything deployment-specific comes from `.env` — nothing is hard-coded.

**The one thing you must set for production: `APP_URL`.** Every canonical URL,
`og:url`, sitemap `<loc>`, and the `Sitemap:` line in robots.txt is built from
it. After editing `.env`, run `php artisan config:clear`.

```dotenv
APP_URL=https://your-real-domain.com     # REQUIRED — drives canonicals, OG, sitemap

# All optional — sensible Best CPMS defaults ship in config/seo.php
SEO_SITE_NAME="Best CPMS"
SEO_TAGLINE="Construction Project Management Software"
SEO_DEFAULT_DESCRIPTION="..."
SEO_DEFAULT_KEYWORDS="construction project management software, ..."
SEO_REGION="IN"                          # primary market emphasis
SEO_OG_IMAGE="/og-image.png"
SEO_LOCALE="en_IN"
SEO_TWITTER_HANDLE="@yourhandle"         # omit to drop the twitter:site tag

# Organization block (richer local SEO) — all optional
SEO_ORG_LEGAL_NAME="Your Company Pvt Ltd"
SEO_ORG_EMAIL="hello@your-domain.com"
SEO_ORG_PHONE="+91-..."
SEO_ORG_LOCALITY="Pune"
SEO_ORG_REGION="Maharashtra"
SEO_ORG_COUNTRY="IN"
```

Per-page overrides come from the Blade views via
`@section('title'|'meta_description'|'meta_keywords'|'canonical'|'robots')`.

## Going live — checklist

1. Set `APP_URL` to the real https domain in `.env`; `php artisan config:clear`.
2. Visit `/sitemap.xml` and `/robots.txt` — confirm the URLs show the real
   domain (not `localhost`).
3. Submit the sitemap in **Google Search Console**: add/verify the domain
   property, then *Sitemaps → enter `sitemap.xml` → Submit*. (Repeat in **Bing
   Webmaster Tools** if you want Bing coverage.)
4. Test a few URLs with Google's **Rich Results Test** and the
   **Facebook Sharing Debugger** / **X Card Validator** to confirm the JSON-LD
   and the `og-image.png` preview render.
5. Optionally set the `SEO_ORG_*` and social (`SOCIAL_*`) env vars for a richer
   Organization panel.

Rankings are earned over weeks as Google crawls and indexes — there's no
instant switch. The foundation here is what lets that process start.

## Regenerating the brand assets

The favicon/OG assets were generated by a one-off script (kept out of the app
runtime) using Python Pillow. The source lives with the build notes; to
re-generate after a palette or wording change, re-run that script — it writes
the four files straight into `public/`. The mark is a rounded indigo
(`#4f46e5`) square with a white "C", matching the nav logo; the OG image is a
1200×630 share card with the keyword headline and a sample programme/Gantt
card in the site palette.

## robots.txt — what's open vs blocked

`Allow: /` for the whole marketing site, with the private application paths
disallowed (`/dashboard`, `/login`, `/register`, `/projects`, `/tasks`,
`/users`, `/finance`, `/planning`, `/reports`, `/subscription`, `/settings`,
`/api/`, `/auth/`). Note the **public** *"our team"* page at `/team` is **not**
blocked — private team management lives at `/users`. Keep that distinction in
mind if you add disallow rules: never disallow a path prefix that a public,
sitemap-listed page also sits under.

## Files

```
config/seo.php                                     # brand/org/default metadata (env-backed)
config/landing.php                                 # Solutions landing-page definitions (keyword cluster)
app/Http/Controllers/SitemapController.php         # /sitemap.xml + /robots.txt (incl. landing pages)
app/Http/Controllers/LandingController.php         # renders config-driven Solutions pages
app/Http/Controllers/PageController.php            # +cpmSoftware()
routes/web.php                                     # +cpm-software, sitemap, robots, landing.* loop
resources/views/layouts/public.blade.php           # <head> metadata + Org/WebSite JSON-LD + @stack('ld') + footer Solutions column
resources/views/public/cpm-software.blade.php      # keyword pillar landing page (+Solutions links)
resources/views/public/landing.blade.php           # one template for every Solutions landing page
resources/views/public/home.blade.php              # +SoftwareApplication JSON-LD
resources/views/public/faq.blade.php               # +FAQPage JSON-LD
resources/views/public/services/{planning,cost,field}.blade.php   # keyword titles + Service/Breadcrumb JSON-LD
public/{favicon.ico,favicon.svg,apple-touch-icon.png,og-image.png}
```
