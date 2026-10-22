# Multi-currency (INR + USD)

Best CPMS sells worldwide, so prices are shown in the visitor's currency — Indian
Rupees (₹) or US Dollars ($). This document explains the one rule that keeps the
feature safe, how a currency is resolved for each request, and how to add
another currency later.

---

## The one rule: display is not charge

There are two distinct jobs a currency does here, and they are deliberately kept
apart:

- **Display currency** — what a visitor *sees* across the marketing site and the
  subscription screens. Resolved per request from a cookie. Can be any
  configured currency.
- **Charge currency** — what a customer is actually *billed* in. Exactly one
  currency carries `charge => true` in `config/billing.php`; today that is
  **INR, via Razorpay**.

> The authoritative charge amount is always recomputed server-side from the
> **charge** currency's `price_per_user` (`SubscriptionController`). The display
> currency, the cookie, and anything the browser posts never influence what is
> billed. Switching the display to USD changes the figures on the page, not the
> rupee amount sent to Razorpay.

Each currency carries its own whole-number **set** price — `₹1000` and `$12` per
user, per month — **not** a live FX conversion. These prices are sourced from
the global `plans` / `plan_prices` tables first (so an operator can edit them
without a deploy), falling back to `config/billing.php` if no plan row exists.
Marketing copy therefore stays
stable (no fluctuating decimals), and the two prices are both "real" advertised
prices rather than one being derived from the other.

USD (and any future currency) is **display-only** until a currency-native
gateway (e.g. Stripe) is added. Until then, on the plan and checkout screens the
USD total is clearly labelled *reference* and a note states the charge is taken
in INR.

---

## Configuration — `config/billing.php`

All currencies live in the `currencies` array, keyed by ISO code. Exactly one
should be flagged `charge => true`.

```php
'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'INR'),

'currencies' => [
    'INR' => [
        'code' => 'INR', 'symbol' => '₹', 'label' => 'Indian Rupee',
        'locale' => 'en-IN', 'price_per_user' => 1000,
        'charge' => true,    // billed via Razorpay
    ],
    'USD' => [
        'code' => 'USD', 'symbol' => '$', 'label' => 'US Dollar',
        'locale' => 'en-US', 'price_per_user' => 12,
        'charge' => false,   // display-only for now (billed in INR)
    ],
],
```

The legacy top-level keys (`currency`, `currency_symbol`, `price_per_user`) are
kept — they remain the authoritative values the Razorpay charge is computed
from, and `price_per_user` here mirrors the charge currency's entry. **Keep this
file cacheable**: pure scalars and arrays only, no closures or `route()`/model
constants, so `php artisan config:cache` still works.

---

## The `App\Support\Currency` helper

A small, package-free static helper (no `ext-intl` dependency) that reads the
config and resolves the active currency. Key methods:

| Method | Returns |
|--------|---------|
| `all()` | all configured currencies, keyed by code |
| `has(?string $code)` | whether we offer that code |
| `default()` | the default display code (validated, safe fallback) |
| `chargeCode()` / `charge()` | the code / config array of the **charge** currency |
| `config(?string $code)` | a currency's config array (always includes `'code'`), falling back to the default |
| `active(?Request)` | the active **display** currency config for the request (from the cookie, validated) |
| `isDisplayOnly(string\|array)` | true when it is *not* the charge currency |
| `pricePerUser(string\|array)` | the whole-unit per-user, per-month set price (DB-first from `plans`/`plan_prices`, config fallback) |
| `format(int\|float, string\|array)` | `symbol + number_format(amount)`, e.g. `₹30,000` |

Server-side formatting uses `number_format` (no intl extension needed);
locale-aware grouping on the live calculators is done client-side via
`Intl.NumberFormat` with each currency's `locale`.

---

## How the active currency is resolved

```
request → cpms_currency cookie → Currency::active() → validated code → config array
                 │
                 └─ missing/invalid → Currency::default() (BILLING_DEFAULT_CURRENCY, else INR)
```

The chosen code is always validated against `has()`, so a tampered or stale
cookie can never force an unsupported state — it simply falls back to the
default.

### The cookie

- Name: **`cpms_currency`** (`Currency::COOKIE`), lifetime one year
  (`Currency::COOKIE_MINUTES = 525600`).
- **Exempt from Laravel's cookie encryption** (`encryptCookies(except: [...])`
  in `bootstrap/app.php`). This is deliberate: the client-side auto-detect
  script writes the same cookie, so JS and the server must share one plain
  value. It is a non-sensitive UI preference, never an authorization input.

### Shared with every view

`App\Http\Middleware\ShareDisplayCurrency` is appended to the **`web`**
middleware group (after cookie decryption). It shares two variables with every
view:

- `$currency` — the active display currency config array
- `$currencies` — all configured currencies

So any Blade view can render prices in the visitor's currency without each
controller having to pass them. (The subscription controller still passes
explicit `displayCurrency` / `chargeCurrency` / `displayIsCharge` values because
those screens need the charge-vs-display distinction spelled out.)

---

## Switching currency

**Route** (`routes/web.php`): a GET link, since it is a display preference, not a
destructive action.

```php
Route::get('currency/{code}', [CurrencyController::class, 'switch'])
    ->where('code', '[A-Za-z]{3}')      // static constraint → route:cache safe
    ->name('currency.switch');
```

`CurrencyController@switch` upper-cases the code, ignores anything `Currency` does
not offer, queues the plain cookie, and redirects back (fallback `pricing`). It
uses **controller-action array syntax** and a **static `where()`** so
`php artisan route:cache` still works — no closures.

**UI** (`resources/views/partials/currency-switcher.blade.php`): a small
segmented control (₹ INR / $ USD) that works without JavaScript — plain links to
`currency.switch`, active option highlighted, `rel="nofollow"` so crawlers don't
index per-currency URLs. It renders only when more than one currency is
configured. Included on the public nav (desktop + mobile) and on the pricing
page.

**First-visit auto-detect** (`layouts/public.blade.php`): a tiny inline script
(no package) runs only when no `cpms_currency` cookie exists yet. It guesses INR
for India (timezone `Asia/Kolkata`/`Calcutta` or an `-IN` locale) and USD
otherwise, writes the cookie, and reloads once if the guess differs from the
server default. Detection is best-effort and wrapped in `try/catch`; visitors
can always override it with the switcher.

---

## Where prices appear

| Surface | Behaviour |
|---------|-----------|
| Public pricing page | Full calculator in the active currency, the other currency as a reference line, on-page switcher, footer note about INR billing |
| Pillar (`cpm-software`), home, landing pages | Hero chips, pricing teasers and FAQ copy show both currencies (active + reference), driven by the shared `$currency` |
| Subscription **plans** | INR total is primary; a `displayIsCharge` check adds a USD *reference* total and a "billed in INR via Razorpay" note only when the display currency isn't the charge currency |
| Subscription **checkout** | Same: INR `Total` and the paid amount are authoritative; a USD "Reference" row + note appear only when relevant. The Razorpay order (`amount` in paise) is unchanged |

On the subscription screens the client-side JS recomputes the USD figure as
`seats × months × usd_price_per_user` purely for display; the charged amount is
the server-computed INR total.

### Structured data (JSON-LD)

The `SoftwareApplication` schema on the home and pillar pages emits an **array of
`Offer`s — one per configured currency** (INR & USD), each with its own `price`
and `priceCurrency` drawn from `config/billing`. This is honest because every
currency has a real set price; it is not an FX-derived figure. See
[SEO.md](SEO.md) for the full structured-data picture.

---

## Adding a currency

1. Add an entry to `currencies` in `config/billing.php` with its `code`,
   `symbol`, `label`, `locale`, and whole-number `price_per_user`. Leave
   `charge => false` unless you are also wiring a gateway that can charge in it.
2. Run `php artisan config:clear` (or `config:cache`).

That's it — the switcher, the shared view data, every price surface, and the
JSON-LD Offers array all pick it up automatically. To make a currency the one
customers are **billed** in, set `charge => true` on it (and only it), make sure
the payment gateway supports it, and confirm `SubscriptionController` computes
the charge from that currency's price.

---

## Why it's safe

- **Display ≠ charge.** The charged amount is recomputed server-side from the
  charge currency; the cookie and browser never set it.
- **Validated input.** The currency code is always checked against the
  configured set, so a tampered cookie or URL can't reach an unsupported state —
  it falls back to the default.
- **No new dependency.** Pure config + a static helper + one middleware; no
  Composer package, no `ext-intl`, no build step.
- **Cache-safe.** `config/billing.php` stays pure arrays (config:cache); the
  switch route uses controller-action syntax and a static `where()`
  (route:cache).
- **Not an authorization input.** The cookie is a UI preference only; tenant
  isolation and billing authorization are unaffected by it.
