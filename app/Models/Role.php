<?php

namespace App\Models;

use App\Support\AppPages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'pages',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pages' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Built-in roles are shared by every company and are not edited here. */
    public function isSystem(): bool
    {
        return $this->company_id === null;
    }

    /**
     * Pages this role may open. A role saved before pages existed falls back
     * to the built-in set for its slug.
     *
     * @return list<string>
     */
    public function pageKeys(): array
    {
        if (is_array($this->pages)) {
            return array_values($this->pages);
        }

        return AppPages::forSlug($this->slug);
    }

    /**
     * @param  Builder<Role>  $query
     * @return Builder<Role>
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where(function (Builder $inner) use ($companyId): void {
            $inner->whereNull('company_id')->orWhere('company_id', $companyId);
        });
    }

    /**
     * Roles this person may give to a team member.
     *
     * @return Collection<int, Role>
     */
    public static function assignableFor(User $actor): Collection
    {
        $roles = static::query()
            ->forCompany($actor->company_id)
            ->where('slug', '!=', User::ROLE_OWNER)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($actor->isAdmin()) {
            return $roles;
        }

        return $roles->filter(function (Role $role) use ($actor): bool {
            if (in_array($role->slug, [User::ROLE_ADMIN, User::ROLE_OWNER], true)) {
                return false;
            }

            foreach ($role->pageKeys() as $page) {
                if (! $actor->hasPage($page)) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /** True when this role id or slug may be assigned by the actor. */
    public static function canBeAssignedBy(User $actor, string $value): bool
    {
        return static::assignableFor($actor)->contains(function (Role $role) use ($value): bool {
            return (string) $role->id === $value || $role->slug === $value;
        });
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
