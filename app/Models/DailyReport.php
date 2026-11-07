<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\DailyReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A daily site report: what happened on one project on one day, how many
 * labour were on site, a note, and photos. One per reporter per project per
 * day; saving again the same day updates it.
 */
class DailyReport extends Model
{
    /** @use HasFactory<DailyReportFactory> */
    use BelongsToCompany, HasFactory;

    /** Trades offered in the app's labour counter; any other name is allowed. */
    public const TRADES = ['Mason', 'Helper', 'Carpenter', 'Bar bender', 'Electrician', 'Plumber', 'Painter', 'Welder', 'Operator'];

    /** How many days back a report may be filed or edited. */
    public const EDIT_WINDOW_DAYS = 3;

    /**
     * company_id and user_id are stamped by the server, never from input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'report_date',
        'work_done',
        'labour_count',
        'labour_breakdown',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'labour_count' => 'integer',
            'labour_breakdown' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DailyReportPhoto::class)->latest();
    }

    /** Still inside the window when the reporter may change it. */
    public function isEditable(): bool
    {
        return $this->report_date !== null
            && $this->report_date->greaterThanOrEqualTo(today()->subDays(self::EDIT_WINDOW_DAYS));
    }
}
