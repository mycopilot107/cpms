<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqItem extends Model
{
    use HasFactory, BelongsToCompany;

    /**
     * company_id and created_by are guarded (stamped server-side). project_id
     * is fillable but every request validates it belongs to the current
     * company. `amount` is recomputed from quantity * rate in the controller,
     * never trusted from the browser.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'item_code',
        'category',
        'description',
        'unit',
        'quantity',
        'rate',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Line total from the current quantity and rate. */
    public function computedAmount(): float
    {
        return round((float) $this->quantity * (float) $this->rate, 2);
    }
}
