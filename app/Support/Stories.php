<?php

namespace App\Support;

use App\Models\SuccessStory;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Read-side helper for customer success stories on the public site.
 *
 * Every method is defensive: if the `success_stories` table doesn't exist yet
 * (migration not run) or the query fails for any reason, it returns an empty
 * collection so the marketing pages still render — they fall back to their
 * built-in sample content.
 */
class Stories
{
    /**
     * Approved stories, newest first, shaped for the public views.
     *
     * @return Collection<int, array{name:string,org:string,stars:int,text:string,initials:string}>
     */
    public static function approved(int $limit = 0): Collection
    {
        try {
            $query = SuccessStory::query()
                ->approved()
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->orderByDesc('id');

            if ($limit > 0) {
                $query->limit($limit);
            }

            return $query->get()->map->toPublicArray();
        } catch (Throwable $e) {
            return collect();
        }
    }

    /**
     * The single story to spotlight on the homepage — the most recently
     * featured approved story, or the most recent approved one, or null.
     */
    public static function spotlight(): ?array
    {
        return self::approved(1)->first();
    }

    /**
     * A simple average rating across approved stories that carry one, or null
     * when there is nothing to average. Kept honest: derived only from real,
     * approved submissions.
     */
    public static function averageRating(): ?float
    {
        try {
            $avg = SuccessStory::query()
                ->approved()
                ->whereNotNull('rating')
                ->avg('rating');

            return $avg !== null ? round((float) $avg, 1) : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /** How many approved stories exist (for "N stories" labels). */
    public static function count(): int
    {
        try {
            return SuccessStory::query()->approved()->count();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
