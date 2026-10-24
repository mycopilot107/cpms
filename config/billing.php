<?php

/*
|--------------------------------------------------------------------------
| Subscription billing
|--------------------------------------------------------------------------
|
| Single source of truth for the paid-plan pricing RULES (seat choices, term
| choices, minimum seats, billing period). Both the plan selection screen and
| the server-side price computation read from here, so the browser is never
| trusted for the amount — the server always recomputes the total from these
| values and the (validated) seat/term selection.
|
| Pricing model: total = months x seats x price_per_user
|   e.g. 12 months x 15 users x ₹1000 = ₹180,000
|
| EDITABLE PRICES LIVE IN THE DATABASE:
|   The per-user prices below are the FALLBACK / seed values. The authoritative,
|   editable prices live in the `plans` + `plan_prices` tables (see the Plan
|   model and App\Support\Currency). This lets prices be changed in future
|   without a code deploy. When no plan row is available (e.g. before the
|   migration runs) the config values here are used, so public pages never
|   break.
|
| MULTI-CURRENCY (Best CPMS sells worldwide):
|   Prices are DISPLAYED in the visitor's currency (see `currencies` below and
|   App\Support\Currency). Each currency carries its own whole-number price —
|   these are SET prices, not live FX conversions, so marketing copy stays
|   stable. Payment is still CHARGED in the currency flagged `charge => true`
|   (INR, via Razorpay); the others are display-only until a currency-native
|   gateway (e.g. Stripe) is added. The authoritative charge amount is always
|   recomputed server-side from the resolved per-user price.
|
| NOTE: keep this file cacheable by `php artisan config:cache` — pure scalars
| and arrays only (no closures, no route()/model constants).
|
*/

return [

    // Currency the company is billed/charged in. Razorpay amounts are charged
    // in the smallest unit (paise for INR), which we derive as amount * 100.
    // This stays the single authoritative CHARGE currency.
    'currency' => env('BILLING_CURRENCY', 'INR'),
    'currency_symbol' => '₹',

    // How the per-user rate is quoted and billed. The term the customer picks
    // (see `term_options`) is counted in these units.
    'billing_period' => 'month',

    // Price per user, per MONTH, in whole rupees — the FALLBACK charge price
    // used only when no `plans`/`plan_prices` row is available. The editable
    // source of truth is the Plan model (DB).
    'price_per_user' => 1000,

    // A company must license at least this many users.
    'min_seats' => 2,

    // Allowed user-count (seat) choices on the plan screen.
    'seat_options' => [2, 3, 4, 5, 10, 15, 20, 25, 50, 100, 200, 250, 500, 1000],

    // Allowed subscription lengths, in MONTHS.
    'term_options' => [1, 3, 6, 12],

    /*
    |--------------------------------------------------------------------------
    | Display currencies
    |--------------------------------------------------------------------------
    | The currency a visitor sees. Resolved per request from the
    | `cpms_currency` cookie (App\Support\Currency), with `default_currency`
    | as the fallback. `price_per_user` here is the SET price advertised in
    | that currency (not an FX conversion) and SEEDS the editable `plan_prices`
    | row for that currency. Currencies flagged `charge => true` can be billed
    | through Razorpay. INR is always chargeable; when more than one is, the
    | company's own currency (or the visitor's display currency) is used if it
    | is chargeable, otherwise INR.
    */
    'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'INR'),

    'currencies' => [
        'INR' => [
            'code' => 'INR',
            'symbol' => '₹',
            'label' => 'Indian Rupee',
            'locale' => 'en-IN',
            'price_per_user' => 1000,
            'charge' => true,   // billed via Razorpay
        ],
        'USD' => [
            'code' => 'USD',
            'symbol' => '$',
            'label' => 'US Dollar',
            'locale' => 'en-US',
            'price_per_user' => 12,
            // Charge USD only after Razorpay has enabled international payments
            // in USD on the account. Until then USD is a display reference and
            // foreign customers are billed in INR.
            'charge' => (bool) env('BILLING_CHARGE_USD', false),
        ],
    ],

];
