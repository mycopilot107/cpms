<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single Razorpay checkout attempt (order → payment).
 *
 * Uses BelongsToCompany so company_id is stamped from the current user and
 * every query is automatically scoped to that company — a payment created by
 * company A can never be read, verified, or mutated by company B.
 */
class Payment extends Model
{
    use HasFactory, BelongsToCompany;

    public const STATUS_CREATED = 'created';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    /**
     * SECURITY: company_id is intentionally NOT fillable — it is stamped
     * server-side by the BelongsToCompany trait, never taken from input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'user_id',
        'plan_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'seats',
        'months',
        'billing_period',
        'price_per_user',
        'amount',
        'currency',
        'status',
        'receipt',
        'failure_reason',
    ];

    protected $casts = [
        'seats' => 'integer',
        'months' => 'integer',
        'plan_id' => 'integer',
        'price_per_user' => 'integer',
        'amount' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** Razorpay charges in the smallest currency unit (paise for INR). */
    public function amountInPaise(): int
    {
        return $this->amount * 100;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
