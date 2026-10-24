<?php

namespace App\Support;

use App\Models\Plan;
use Illuminate\Http\Request;
use Throwable;

/**
 * Display-currency helper for Best CPMS (sells worldwide → prices shown in INR/USD).
 *
 * All currencies, their symbols and SET per-user prices live in
 * config/billing.php. This class resolves the *active* display currency for a
 * request (from the `cpms_currency` cookie, validated), and formats amounts.
 *
 * It is DISPLAY ONLY. The authoritative charge amount is still computed
 * server-side in SubscriptionController from the charge currency's price — the
 * browser (and the chosen display currency) never set what is billed.
 */
class Currency
{
    /** Cookie that stores the visitor's chosen display currency. */
    public const COOKIE = 'cpms_currency';

    /** One year, in minutes — the switch cookie lifetime. */
    public const COOKIE_MINUTES = 525600;

    /**
     * All configured display currencies, keyed by ISO code.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return (array) config('billing.currencies', []);
    }

    /** Is this a currency we actually offer? */
    public static function has(?string $code): bool
    {
        return $code !== null && array_key_exists(strtoupper($code), static::all());
    }

    /** The default display currency code (validated, with a safe fallback). */
    public static function default(): string
    {
        $code = strtoupper((string) config('billing.default_currency', config('billing.currency', 'INR')));

        if (static::has($code)) {
            return $code;
        }

        $first = array_key_first(static::all());

        return $first !== null ? (string) $first : 'INR';
    }

    /** The currency payments are actually CHARGED in (Razorpay). */
    public static function chargeCode(): string
    {
        foreach (static::all() as $code => $cur) {
            if (! empty($cur['charge'])) {
                return (string) $code;
            }
        }

        return strtoupper((string) config('billing.currency', 'INR'));
    }

    /**
     * Config array for a currency code, falling back to the default currency.
     * Always includes a 'code' key.
     *
     * @return array<string, mixed>
     */
    public static function config(?string $code): array
    {
        $code = strtoupper((string) $code);
        $all = static::all();

        if (! isset($all[$code])) {
            $code = static::default();
        }

        return ($all[$code] ?? []) + ['code' => $code];
    }

    /** The config array for the charge currency. */
    public static function charge(): array
    {
        return static::config(static::chargeCode());
    }

    /** Whether Razorpay can bill in this currency. */
    public static function isChargeable(?string $code): bool
    {
        $code = strtoupper((string) $code);

        return static::has($code) && ! empty(static::all()[$code]['charge']);
    }

    /**
     * The currency to bill in: the first preferred code that is chargeable
     * (e.g. the company's currency, then the visitor's display currency),
     * else the default charge currency.
     *
     * @return array<string, mixed>
     */
    public static function chargeFor(?string ...$preferred): array
    {
        foreach ($preferred as $code) {
            if (static::isChargeable($code)) {
                return static::config($code);
            }
        }

        return static::charge();
    }

    /**
     * Resolve the active DISPLAY currency for a request, from the
     * `cpms_currency` cookie (validated), else the default. Returns the
     * currency's config array (code, symbol, label, locale, price_per_user, …).
     *
     * @return array<string, mixed>
     */
    public static function active(?Request $request = null): array
    {
        $request ??= request();

        $chosen = $request?->cookie(static::COOKIE);
        $code = is_string($chosen) ? strtoupper($chosen) : null;

        return static::config(static::has($code) ? $code : static::default());
    }

    /** Whether a currency is display-only (i.e. not the charge currency). */
    public static function isDisplayOnly(string|array $currency): bool
    {
        $cur = is_array($currency) ? $currency : static::config($currency);

        return empty($cur['charge']);
    }

    /** Request-level cache of the current plan's price map (by currency code). */
    protected static ?array $planPrices = null;

    /**
     * Per-user prices from the editable plan (DB), keyed by uppercase currency
     * code. Memoized for the request; an empty array means no plan is available
     * (callers fall back to config). Wrapped so a missing table — e.g. before
     * the plans migration runs — never breaks a page.
     *
     * @return array<string, int>
     */
    protected static function planPrices(): array
    {
        if (static::$planPrices !== null) {
            return static::$planPrices;
        }

        try {
            $plan = Plan::current();
            static::$planPrices = $plan ? $plan->priceMap() : [];
        } catch (Throwable $e) {
            static::$planPrices = [];
        }

        return static::$planPrices;
    }

    /**
     * Per-user, per-period price for a currency, in whole units.
     *
     * DB-first: the editable plan price wins; config/billing.php is the
     * fallback — used before the plans migration runs, or if a currency has no
     * plan_prices row yet.
     */
    public static function pricePerUser(string|array $currency): int
    {
        $cur = is_array($currency) ? $currency : static::config($currency);
        $code = strtoupper((string) ($cur['code'] ?? ''));

        $fromPlan = static::planPrices()[$code] ?? null;
        if ($fromPlan !== null) {
            return (int) $fromPlan;
        }

        return (int) ($cur['price_per_user'] ?? config('billing.price_per_user', 0));
    }

    /**
     * How the rate is quoted/billed ('month') — from the current plan if one is
     * available, else config/billing.php. Drives "/ user / month" copy and is
     * stored on each payment/subscription.
     */
    public static function billingPeriod(): string
    {
        try {
            $plan = Plan::current();
            if ($plan && ! empty($plan->billing_period)) {
                return (string) $plan->billing_period;
            }
        } catch (Throwable $e) {
            // fall through to config
        }

        return (string) config('billing.billing_period', 'month');
    }

    /**
     * Format a whole-number amount with the currency symbol and grouped
     * digits, e.g. (30000, 'INR') => "₹30,000". Uses number_format (no intl
     * extension dependency); locale-aware grouping is handled client-side by
     * the calculators via Intl.NumberFormat.
     */
    public static function format(int|float $amount, string|array $currency): string
    {
        $cur = is_array($currency) ? $currency : static::config($currency);
        $symbol = (string) ($cur['symbol'] ?? '');

        return $symbol.number_format((float) $amount);
    }
}
