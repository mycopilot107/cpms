<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_ENGINEER = 'engineer';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_WORKER = 'worker';

    public const ROLE_ACCOUNTANT = 'accountant';

    public const ROLES = [
        self::ROLE_OWNER,
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_ENGINEER,
        self::ROLE_SUPERVISOR,
        self::ROLE_WORKER,
        self::ROLE_ACCOUNTANT,
    ];

    /**
     * Mass-assignable attributes.
     *
     * SECURITY: company_id, role_id and status are intentionally NOT fillable.
     * They control tenant ownership and privilege level, so they must be set
     * explicitly in server-side code (registration, user management) and never
     * from raw request input — otherwise a user could escalate their role or
     * jump tenants by posting company_id / role_id fields.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (User $user): void {
            if ($user->wasChanged('password') || ($user->wasChanged('status') && ! $user->isActive())) {
                ApiToken::where('user_id', $user->id)
                    ->whereNull('revoked_at')
                    ->update(['revoked_at' => now()]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Point this account at a role by its slug. role_id stays guarded. */
    public function assignRole(string $slug): void
    {
        $this->role_id = Role::idFor($slug);
        $this->unsetRelation('role');
    }

    /*
    |--------------------------------------------------------------------------
    | Role / status helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole(string $role): bool
    {
        return $this->role?->slug === $role;
    }

    public function isOwner(): bool
    {
        return $this->hasRole(self::ROLE_OWNER);
    }

    /** Owners and admins can manage the company and its users. */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_OWNER) || $this->hasRole(self::ROLE_ADMIN);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Platform-wide super admin: may view every company on the platform.
     *
     * This flag is deliberately not fillable. It must only be changed by a
     * trusted administrator or a controlled database/migration operation.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /*
    |--------------------------------------------------------------------------
    | Module access helpers
    |--------------------------------------------------------------------------
    | Finance (BOQ / expenses / payments / budget) and Reports are management
    | information, so they are limited to owners, admins, project managers and
    | accountants. Planning (milestones) follows the task-management roles.
    */

    /** Roles allowed to see and manage finance + reports data. */
    public const FINANCE_ROLES = [
        self::ROLE_OWNER,
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_ACCOUNTANT,
    ];

    /** Roles allowed to create / edit planning milestones. */
    public const PLANNING_ROLES = [
        self::ROLE_OWNER,
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_SUPERVISOR,
    ];

    public function canViewFinance(): bool
    {
        return $this->isActive() && in_array($this->role?->slug, self::FINANCE_ROLES, true);
    }

    public function canManageFinance(): bool
    {
        return $this->canViewFinance();
    }

    public function canViewReports(): bool
    {
        return $this->isActive() && in_array($this->role?->slug, self::FINANCE_ROLES, true);
    }

    public function canManagePlanning(): bool
    {
        return $this->isActive() && in_array($this->role?->slug, self::PLANNING_ROLES, true);
    }
}
