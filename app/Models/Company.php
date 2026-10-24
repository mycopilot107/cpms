<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Company extends Model
{
    use HasFactory;

    /** Length of the free trial, in days. */
    public const TRIAL_DAYS = 15;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_INACTIVE = 'inactive';

    /** Access state shown in the platform console. */
    public const SUB_STATE_SUBSCRIBED = 'subscribed';

    public const SUB_STATE_TRIAL = 'trial';

    public const SUB_STATE_EXPIRED = 'expired';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'gst_number',
        'logo',
        'status',
        'trial_started_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** The owner account created during company registration. */
    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('role_id', Role::idFor(User::ROLE_OWNER));
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Trial helpers
    |--------------------------------------------------------------------------
    */

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function trialExpired(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isPast();
    }

    /** Whole days left in the trial (0 once expired). */
    public function trialDaysRemaining(): int
    {
        if ($this->trial_ends_at === null || $this->trial_ends_at->isPast()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($this->trial_ends_at, false));
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription & seats
    |--------------------------------------------------------------------------
    */

    /** Cache within a request so the gate/nav/seat-check don't re-query. */
    private ?Subscription $activeSubscriptionCache = null;

    private bool $activeSubscriptionResolved = false;

    /** The current paid subscription that is active and not past its end date. */
    public function activeSubscription(): ?Subscription
    {
        if ($this->activeSubscriptionResolved) {
            return $this->activeSubscriptionCache;
        }

        $this->activeSubscriptionResolved = true;

        return $this->activeSubscriptionCache = $this->subscriptions()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->orderByDesc('ends_at')
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /** The single check the subscription gate uses. */
    public function hasAccess(): bool
    {
        return $this->isOnTrial() || $this->hasActiveSubscription();
    }

    /**
     * How this company currently reaches the app, for the platform console:
     * a current paid subscription, an unexpired trial, or neither (expired).
     *
     * @return self::SUB_STATE_*
     */
    public function subscriptionState(): string
    {
        if ($this->hasActiveSubscription()) {
            return self::SUB_STATE_SUBSCRIBED;
        }

        if ($this->isOnTrial()) {
            return self::SUB_STATE_TRIAL;
        }

        return self::SUB_STATE_EXPIRED;
    }

    /**
     * The date the company's access runs until. While subscribed this is the
     * active subscription's end date; on trial it is the trial end date; once
     * lapsed it is the most recent expiry we know about (so the console can
     * show "expired on …"). Null only when nothing has ever been set.
     */
    public function accessEndsAt(): ?Carbon
    {
        if ($this->hasActiveSubscription()) {
            return $this->activeSubscription()?->ends_at;
        }

        if ($this->isOnTrial()) {
            return $this->trial_ends_at;
        }

        $lastSubscription = $this->subscriptions()
            ->orderByDesc('ends_at')
            ->first();

        return $lastSubscription?->ends_at ?? $this->trial_ends_at;
    }

    /** Purchased user seats, or null when there is no seat-limited plan (e.g. on trial). */
    public function seatLimit(): ?int
    {
        return $this->activeSubscription()?->seats;
    }

    /** Every user account counts as one seat. */
    public function usedSeats(): int
    {
        return $this->users()->count();
    }

    /** Seats still available on the active plan (null = unlimited / on trial). */
    public function seatsRemaining(): ?int
    {
        $limit = $this->seatLimit();

        return $limit === null ? null : max(0, $limit - $this->usedSeats());
    }

    /**
     * May another user be added right now?
     *
     * During the trial (or with no seat-limited plan) there is no cap; once a
     * paid plan is active the company is limited to the seats it purchased.
     */
    public function canAddUser(): bool
    {
        $remaining = $this->seatsRemaining();

        return $remaining === null || $remaining > 0;
    }
}
