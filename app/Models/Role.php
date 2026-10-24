<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

/**
 * A company-account role. Users store role_id, not a free-text role name.
 * The slug is the stable key the rest of the application checks.
 */
class Role extends Model
{
    /** @var array<string, int>|null */
    protected static ?array $idsBySlug = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Primary key for a known slug, such as "owner" or "worker". */
    public static function idFor(string $slug): int
    {
        if (static::$idsBySlug === null) {
            static::$idsBySlug = static::query()->pluck('id', 'slug')->all();
        }

        $id = static::$idsBySlug[$slug] ?? null;

        if ($id === null) {
            throw new InvalidArgumentException("Unknown role [{$slug}].");
        }

        return (int) $id;
    }
}
