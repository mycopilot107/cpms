<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Throwable;

/**
 * A sellable subscription plan and its editable per-user prices.
 *
 * Plans are GLOBAL — the same catalogue applies to every company, so this model
 * is deliberately NOT tenant-scoped (no BelongsToCompany trait). Prices live in
 * the related `plan_prices` rows and can be edited without a code deploy; see
 * App\Support\Currency, which reads them (with config/billing.php as fallback).
 */
class Plan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'billing_period',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /** Request-level memoization of the "current" plan lookup. */
    protected static ?self $currentCache = null;
    protected static bool $currentResolved = false;

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    /**
     * The plan customers are sold today: the active default, otherwise any
     * active plan (lowest id). Resolved once per request and memoized.
     *
     * Returns null — so callers fall back to config/billing.php — if the plans
     * table can't be queried yet (e.g. before this feature's migration runs),
     * which keeps public pricing pages from breaking.
     */
    public static function current(): ?self
    {
        if (self::$currentResolved) {
            return self::$currentCache;
        }

        self::$currentResolved = true;

        try {
            self::$currentCache = static::query()
                ->with('prices')
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->first();
        } catch (Throwable $e) {
            self::$currentCache = null;
        }

        return self::$currentCache;
    }

    /**
     * Per-user prices keyed by uppercase currency code,
     * e.g. ['INR' => 1000, 'USD' => 12].
     */
    public function priceMap(): array
    {
        return $this->prices
            ->mapWithKeys(fn (PlanPrice $price) => [
                strtoupper($price->currency) => (int) $price->price_per_user,
            ])
            ->all();
    }

    /** The per-user price for one currency, or null if not set. */
    public function priceFor(string $currency): ?int
    {
        return $this->priceMap()[strtoupper($currency)] ?? null;
    }
}
