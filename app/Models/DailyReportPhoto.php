<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Services\PhotoStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** A photo on a daily site report. Tenant-scoped like the report. */
class DailyReportPhoto extends Model
{
    use BelongsToCompany;

    /**
     * Everything except the caption is set by the server.
     *
     * @var list<string>
     */
    protected $fillable = [
        'caption',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'taken_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (DailyReportPhoto $photo): void {
            Storage::disk(PhotoStore::DISK)->delete([$photo->path, $photo->thumb_path]);
        });
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $user->company_id === $this->company_id
            && ($this->user_id === $user->id || $user->canManagePlanning());
    }
}
