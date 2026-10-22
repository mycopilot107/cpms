<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer success story.
 *
 * Submitted by a signed-in user from inside the app, then reviewed by a site
 * administrator (see config/stories.php `admin_email`). Only `approved` rows
 * are ever shown publicly. Because these rows are shown on a *public* page,
 * they are deliberately NOT tenant-scoped: the moderation gate — not a
 * company scope — is what protects writes and the private list.
 */
class SuccessStory extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    protected $fillable = [
        'name',
        'role',
        'organisation',
        'email',
        'story',
        'rating',
        'status',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'rating'       => 'integer',
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
    ];

    // -- Relations --------------------------------------------------------

    /** The company the submitter belonged to (nullable; for context/audit). */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** The user who submitted it (nullable; nullOnDelete). */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // -- Scopes -----------------------------------------------------------

    /** Only rows safe to show on the public site. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    // -- State helpers ----------------------------------------------------

    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isPending(): bool  { return $this->status === self::STATUS_PENDING; }

    /** Initials for the round avatar chip, e.g. "Meera Kulkarni" → "MK". */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $parts = array_filter($parts);

        $letters = array_map(
            fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)),
            array_slice(array_values($parts), 0, 2),
        );

        return $letters ? implode('', $letters) : 'C';
    }

    /**
     * Shape an approved story into the simple array the public views already
     * render (name / org / stars / text), so the home + reviews templates can
     * read live data with the same markup they used for sample content.
     */
    public function toPublicArray(): array
    {
        $org = trim((string) $this->organisation);
        if ($this->role) {
            $org = $org !== '' ? $this->role.', '.$org : $this->role;
        }

        return [
            'name'  => $this->name,
            'org'   => $org,
            'stars' => (int) ($this->rating ?: 5),
            'text'  => $this->story,
            'initials' => $this->initials(),
        ];
    }
}
