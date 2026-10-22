<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    public const PLAN_TRIAL = 'trial';
    public const PLAN_PAID = 'paid';

    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'plan',
        'plan_id',
        'billing_period',
        'status',
        'seats',
        'billing_months',
        'price_per_user',
        'amount',
        'currency',
        'trial_starts_at',
        'trial_ends_at',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'seats' => 'integer',
        'billing_months' => 'integer',
        'plan_id' => 'integer',
        'price_per_user' => 'integer',
        'amount' => 'integer',
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The purchased pricing plan.
     *
     * Named `pricingPlan` (not `plan`) on purpose: `plan` is already a string
     * column holding 'trial' | 'paid', so a relation called `plan()` would be
     * shadowed by that attribute on property access. Use `pricingPlan` to reach
     * the Plan model / its prices.
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function onTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Active AND not past its end date. This is the real "does this grant
     * access right now" check used by the subscription gate.
     */
    public function isCurrentlyActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
