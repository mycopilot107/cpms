<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The per-user price of a plan in one currency (e.g. Standard / INR / ₹1000).
 *
 * One row per (plan, currency). Editing `price_per_user` here changes the
 * advertised and charged price without a code deploy.
 */
class PlanPrice extends Model
{
    protected $fillable = [
        'plan_id',
        'currency',
        'price_per_user',
    ];

    protected $casts = [
        'price_per_user' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
