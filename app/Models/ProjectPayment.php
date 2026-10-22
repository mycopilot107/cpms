<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPayment extends Model
{
    use HasFactory, BelongsToCompany;

    public const DIRECTION_INCOMING = 'incoming';
    public const DIRECTION_OUTGOING = 'outgoing';

    public const DIRECTIONS = [
        self::DIRECTION_INCOMING,
        self::DIRECTION_OUTGOING,
    ];

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK = 'bank_transfer';
    public const METHOD_CHEQUE = 'cheque';
    public const METHOD_UPI = 'upi';
    public const METHOD_CARD = 'card';
    public const METHOD_OTHER = 'other';

    public const METHODS = [
        self::METHOD_CASH,
        self::METHOD_BANK,
        self::METHOD_CHEQUE,
        self::METHOD_UPI,
        self::METHOD_CARD,
        self::METHOD_OTHER,
    ];

    /**
     * company_id and created_by are guarded. project_id is validated per-company
     * in the form request.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'direction',
        'party_name',
        'amount',
        'payment_date',
        'method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isIncoming(): bool
    {
        return $this->direction === self::DIRECTION_INCOMING;
    }
}
