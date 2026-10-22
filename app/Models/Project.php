<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, BelongsToCompany;

    public const STATUS_PLANNING = 'planning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PLANNING,
        self::STATUS_ACTIVE,
        self::STATUS_ON_HOLD,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    /**
     * company_id and created_by are intentionally NOT fillable:
     * company_id is stamped by the BelongsToCompany trait, created_by is set
     * from the authenticated user in the controller. Neither is ever trusted
     * from request input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'project_code',
        'client_name',
        'client_contact',
        'site_address',
        'start_date',
        'end_date',
        'budget',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function boqItems(): HasMany
    {
        return $this->hasMany(BoqItem::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function projectPayments(): HasMany
    {
        return $this->hasMany(ProjectPayment::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Average completion across this project's tasks (0 when none). */
    public function progressPercent(): int
    {
        return (int) round((float) $this->tasks()
            ->where('status', '!=', Task::STATUS_CANCELLED)
            ->avg('progress'));
    }

    /*
    |--------------------------------------------------------------------------
    | Finance roll-ups
    |--------------------------------------------------------------------------
    | Convenience helpers for the Finance overview and Cost report. For list
    | screens prefer withSum() to avoid N+1 queries; these are for detail views.
    */

    /** Total priced value of the BOQ. */
    public function boqTotal(): float
    {
        return (float) $this->boqItems()->sum('amount');
    }

    /** Total actual spend recorded against the project. */
    public function expenseTotal(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    /** Money received from the client. */
    public function paymentsReceived(): float
    {
        return (float) $this->projectPayments()
            ->where('direction', ProjectPayment::DIRECTION_INCOMING)
            ->sum('amount');
    }

    /** Money paid out to vendors / subcontractors. */
    public function paymentsPaid(): float
    {
        return (float) $this->projectPayments()
            ->where('direction', ProjectPayment::DIRECTION_OUTGOING)
            ->sum('amount');
    }

    /** Budget minus actual spend (positive = under budget). Null if no budget set. */
    public function budgetVariance(): ?float
    {
        if ($this->budget === null) {
            return null;
        }

        return (float) $this->budget - $this->expenseTotal();
    }
}
