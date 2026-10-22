# Best CPMS — Subscription billing (Razorpay)

When a company's 15-day free trial ends, it must buy a **paid** subscription
to keep using Best CPMS. This document covers the pricing model, the Razorpay
integration, and how to run and test it.

---

## 1. Pricing

```
total = months × users × price-per-user
```

- **Rate:** ₹1000 **or** $12 per user, per month (see currencies below).
- **Duration:** 1, 3, 6, or 12 months.
- **Users (seats):** 2, 3, 4, 5, 10, 15, 20, 25, 50, 100, 200, 250, 500, 1000 — **minimum 2**.

Example: 15 users for 12 months = `12 × 15 × 1000` = **₹180,000** (shown as **$2,160** when the visitor is viewing USD).

The seat and term lists, the minimum, and the fallback prices live in
[`config/billing.php`](../config/billing.php). Live prices are **DB-sourced**:
the app reads the active row from the `plans` / `plan_prices` tables first
(so an operator can change the price without touching code — see §10), and
falls back to `config/billing.php` only if no plan row is available. The server
**always recomputes the amount** from the resolved price and the validated
selection; the number shown in the browser is never trusted.

### Plans table (editable pricing)

Prices are stored in two global tables — `plans` (the plan definition, with
`name`, `code`, `billing_period`, `is_active`, `is_default`) and `plan_prices`
(one row per plan + currency, with `currency` and `price_per_user`). A migration
seeds a single **Standard** plan (code `standard`, default) with one price row
per currency in `config/billing.php`. [`App\Models\Plan::current()`](../app/Models/Plan.php)
resolves the active default plan (memoised, with a `try/catch` so public pages
still render if the migration hasn't run yet); [`App\Support\Currency::pricePerUser()`](../app/Support/Currency.php)
returns the plan's price for a currency, falling back to config. To change the
price going forward, update the `plan_prices` row — no deploy needed.


### Multi-currency (INR + USD)

Best CPMS sells worldwide, so prices are **displayed** in the visitor's currency —
Indian Rupees (₹) or US Dollars ($) — with the other currency shown alongside.
Each currency carries its own whole-number **set** price (not a live FX
conversion), so marketing copy stays stable — sourced from the `plans` /
`plan_prices` tables (§1). See
[MULTI-CURRENCY.md](MULTI-CURRENCY.md) for the full design.

Payment is still **charged** in the currency flagged `charge => true` in
`config/billing.php` — **INR, via Razorpay**. USD (and any future currency) is
**display-only** until a currency-native gateway (e.g. Stripe) is added, so on
the plan and checkout screens the USD figure is clearly labelled "reference" and
a note states the charge is taken in INR. The authoritative charge amount is
unchanged: it is recomputed server-side from the INR `price_per_user`.

---

## 2. One-time setup

1. Create a Razorpay account and get API keys from
   <https://dashboard.razorpay.com/app/keys>. Use **Test Mode** keys
   (`rzp_test_…`) while developing.

2. Add them to your `.env` (never commit real keys):

   ```
   RAZORPAY_KEY=rzp_test_xxxxxxxxxxxxx
   RAZORPAY_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx
   ```

3. Run the new migrations:

   ```sh
   php artisan migrate
   ```

   This adds the paid-plan columns to `subscriptions` (including the
   `months` / `billing_months` rename), creates the
   `payments` table, and creates + seeds the `plans` / `plan_prices` tables.

If the keys are absent the plan screen still loads but the "Proceed to payment"
button is disabled, so nothing breaks in an unconfigured environment.

---

## 3. The flow

```
Trial ends
   │
   ▼
/subscription/expired ──(admin clicks "Choose a plan")──▶ /subscription/plans
                                                              │  pick users + months
                                                              ▼
                                          POST /subscription/checkout
                                          • validates seats/months (server-side)
                                          • recomputes amount
                                          • creates a Payment (status=created)
                                          • creates a Razorpay Order
                                                              │
                                                              ▼
                                          Razorpay Checkout modal (checkout.js)
                                          • user pays
                                          • returns payment_id + signature
                                                              │
                                                              ▼
                                          POST /subscription/verify
                                          • verifies HMAC-SHA256 signature
                                          • marks Payment paid
                                          • creates an active Subscription
                                            (ends_at = now + months)
                                                              │
                                                              ▼
                                          Redirect to /dashboard (gate lifts)
```

Only **owners and admins** can reach these routes (enforced in the controller).
The billing routes deliberately sit **outside** the `subscription` gate so an
expired company can still pay — gating them would cause a redirect loop back to
the expired page.

---

## 4. Data model

**`subscriptions`** (extended) — the entitlement. A paid row has
`plan = 'paid'`, `status = 'active'`, `seats`, `billing_months`,
`billing_period`, `plan_id` (FK → `plans`), `price_per_user`, `amount`,
`currency`, `starts_at`, `ends_at`. The
`EnsureSubscriptionActive` gate grants access while a company `isOnTrial()` **or**
has a subscription that is active and not past `ends_at`.

**`payments`** (new) — the transaction ledger. One row per checkout attempt,
holding the Razorpay `order_id` / `payment_id` / `signature`, the purchased
seats/months, the `plan_id` + `billing_period` it was priced against, the
server-computed amount, and a `status` of
`created → paid | failed`. It carries `company_id` and uses the same
`BelongsToCompany` tenant scope as every other owned model, so one company can
never read or verify another's payment.

**`plans`** / **`plan_prices`** (new, global — *not* tenant-scoped) — the
editable price catalogue described in §1. `Subscription` reaches its plan via
the `pricingPlan()` relation (named that way because the model already has a
`plan` string column).

---

## 5. Seat cap

Because pricing is per user, the purchased seat count is a **hard cap** on team
size. Adding a team member is blocked once a company reaches its licensed seats
(`Company::canAddUser()`), with a message pointing to an upgrade. There is **no
cap during the trial** (or when no seat-limited plan is active). Existing
accounts are never removed if a company later buys fewer seats than its current
headcount — checkout simply requires choosing at least the current user count.

To make seats advisory instead of a hard cap, remove the `withValidator` block
in `app/Http/Requests/StoreUserRequest.php`.

---

## 6. Testing in Test Mode

With `rzp_test_…` keys, use Razorpay's test instruments in the modal — e.g. card
`4111 1111 1111 1111`, any future expiry, any CVV, and any name; complete the
OTP step with the on-screen test OTP. On success you'll land on the dashboard
with an active subscription. See Razorpay's docs for the current list of test
cards and UPI handles.

Smoke test end to end:

1. Register a company, then in the DB set its `trial_ends_at` to the past (or
   wait) so the gate sends you to `/subscription/expired`.
2. As the owner, click **Choose a plan**, pick users + months, and pay with a
   test card.
3. Confirm you're redirected to the dashboard and the trial banner is gone.
4. Check the `subscriptions` row (`status = active`, correct `ends_at`) and the
   `payments` row (`status = paid`, populated Razorpay ids).

---

## 7. Security notes

- **No secrets in source.** Keys are read only from `config/services.razorpay`
  which reads `.env`.
- **Server-authoritative price.** The amount is recomputed from the resolved
  plan price (and the `config/billing` fallback) plus the validated
  seats/months; the client value is ignored.
- **Signature verification.** The callback is trusted only if
  `hash_hmac('sha256', "{order_id}|{payment_id}", secret)` matches the returned
  signature, compared with `hash_equals` (timing-safe).
- **Idempotent.** A already-paid payment won't create a second subscription on a
  page refresh.
- **Tenant-scoped.** Payments and subscriptions are isolated per company by the
  same global scope as the rest of the app.

---

## 8. Implementation note — no Composer package

This integration talks to the Razorpay **REST API** directly via Laravel's HTTP
client and verifies the signature with `hash_hmac` — there is **no
`razorpay/razorpay` SDK dependency**, mirroring the app's custom (no-Sanctum)
token guard. Nothing extra needs to be installed; just add the keys and migrate.
If you prefer the official SDK you can `composer require razorpay/razorpay` and
swap the calls inside `app/Services/RazorpayService.php`, but it isn't required.

---

## 9. Not yet included

Webhooks (server-to-server confirmation, useful if the browser closes before the
callback), invoices/receipts (PDF or email), auto-renewal via Razorpay
Subscriptions, and proration on mid-term upgrades. Each is additive and doesn't
change the tenancy or auth design.
