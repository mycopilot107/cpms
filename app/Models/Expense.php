<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory, BelongsToCompany;

    public const CATEGORY_MATERIAL = 'material';
    public const CATEGORY_LABOUR = 'labour';
    public const CATEGORY_EQUIPMENT = 'equipment';
    public const CATEGORY_TRANSPORT = 'transport';
    public const CATEGORY_SUBCONTRACTOR = 'subcontractor';
    public const CATEGORY_OVERHEAD = 'overhead';
    public const CATEGORY_OTHER = 'other';

    public const CATEGORIES = [
        self::CATEGORY_MATERIAL,
        self::CATEGORY_LABOUR,
        self::CATEGORY_EQUIPMENT,
        self::CATEGORY_TRANSPORT,
        self::CATEGORY_SUBCONTRACTOR,
        self::CATEGORY_OVERHEAD,
        self::CATEGORY_OTHER,
    ];

    /**
     * company_id and created_by are guarded. project_id is validated per-company
     * in the form request.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'vendor',
        'reference_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
