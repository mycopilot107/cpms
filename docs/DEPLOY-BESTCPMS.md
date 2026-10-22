# Launching as "Best CPMS" on bestcpms.com

This note covers the brand rename (CPMS → **Best CPMS**) and the switch to the
production domain **https://bestcpms.com**. The code changes are already in the
repo; the steps below are what you do on the server to make them live.

## What changed in the code

- **Brand text** renamed `CPMS` → `Best CPMS` everywhere user-visible: the
  logged-in app UI, the public marketing site, SEO titles/descriptions/JSON-LD
  defaults (`config/seo.php`), `config/landing.php` copy, email copy, the
  Flutter app's display name, the favicon label, and the `docs/`.
- **Domain** set to `https://bestcpms.com` via `APP_URL` in `.env` and
  `.env.example`. Canonical tags, `sitemap.xml`, `robots.txt` and Open Graph
  URLs all derive from `APP_URL`, so they follow automatically.
- **App name** default is now `Best CPMS` (`config/app.php`), so the mail
  "from" name and other framework uses of the app name are branded.
- **APK** is served to users as `BestCPMS-Field.apk` (the on-disk file stays
  `public/downloads/cpms-field-app.apk`).

Code identifiers were deliberately **left unchanged** so nothing breaks: the
Flutter package id `cpms_mobile`, the `cpms_currency` cookie key, and the
`cpms-field-app.apk` build artifact name.

## Server setup (do once when you deploy)

### 1. Environment

Your production server has its own `.env`. Set at least:

```env
APP_NAME="Best CPMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bestcpms.com
```

The repo `.env` already has `APP_NAME` and `APP_URL` set; `APP_ENV`/`APP_DEBUG`
are left as-is for local work — flip them to `production` / `false` on the
server.

### 2. Clear and rebuild caches (important)

Blade views and (on most servers) config are cached. Until you clear them the
site can still show the old brand or old domain:

```sh
php artisan optimize:clear      # clears config, route, view and app caches
# then, for production performance, re-cache:
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. OAuth redirect URIs

Google and Facebook login callbacks are built from `APP_URL`, so they are now:

- `https://bestcpms.com/auth/google/callback`
- `https://bestcpms.com/auth/facebook/callback`

Add these exact URLs as authorized redirect URIs in the Google Cloud and
Facebook developer consoles, or social login will fail with a redirect error.

### 4. HTTPS / SSL

`APP_URL` uses `https`. Make sure a valid TLS certificate is installed for
`bestcpms.com` and decide whether `www.bestcpms.com` should redirect to the
apex (or vice-versa) so you have one canonical host.

### 5. SEO

- No code change needed — canonical, `sitemap.xml`, `robots.txt` and OG tags
  all point at `https://bestcpms.com` once `APP_URL` is set.
- Submit `https://bestcpms.com/sitemap.xml` in Google Search Console.
- Optional brand/SEO overrides (`SEO_SITE_NAME`, `SEO_TAGLINE`, …) are
  documented in `.env.example`.

### 6. Mobile app (only if you distribute the APK)

Rebuild the field app pointing at the live API, then it is served from
`/get-the-app`:

```sh
cd mobile
./build_apk.sh https://bestcpms.com/api      # macOS / Linux
build_apk.bat https://bestcpms.com/api       # Windows
```

Running over HTTPS also avoids the cleartext-HTTP caveat described in
`docs/MOBILE-BUILD.md`.

## Follow-up: social share image

`public/og-image.png` is a 1200×630 raster with the old brand baked into it, so
it can't be renamed by a text edit. Regenerate it with "Best CPMS" and replace
the file (the favicon's abstract mark was kept and only its label updated). Ask
and I can produce a new share image.

## Back to local development

The committed `.env` now targets production. For local work, set
`APP_URL=http://localhost:8000` (and `APP_ENV=local`, `APP_DEBUG=true`) in your
local `.env`, then `php artisan config:clear`.
