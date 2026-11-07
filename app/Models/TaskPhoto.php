<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Services\PhotoStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A photo attached to a task. Tenant-scoped like the task itself, so a photo
 * id from another company resolves to nothing.
 */
class TaskPhoto extends Model
{
    use BelongsToCompany;

    public const DISK = PhotoStore::DISK;

    /**
     * company_id, task_id, user_id and the file paths are set by the server
     * (TaskPhotoStore), never from request input.
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
        static::deleted(function (TaskPhoto $photo): void {
            Storage::disk(self::DISK)->delete([$photo->path, $photo->thumb_path]);
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The uploader, or anyone who plans the company's work, may remove it. */
    public function canBeDeletedBy(User $user): bool
    {
        return $user->company_id === $this->company_id
            && ($this->user_id === $user->id || $user->canManagePlanning());
    }
}
