<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Platform console for super admins (see config/platform.php).
 *
 * This is the one place in the app that deliberately crosses the tenant
 * boundary: it lists EVERY company and its subscription / trial expiry. Access
 * is restricted by the `super-admin` middleware; the controller itself is
 * strictly read-only and changes nothing about any company.
 */
class PlatformController extends Controller
{
    /** Company status values that may be used to filter the list. */
    private const COMPANY_STATUSES = [
        Company::STATUS_ACTIVE,
        Company::STATUS_SUSPENDED,
        Company::STATUS_INACTIVE,
    ];

    /**
     * Every company, with its access state and expiry, plus search / filter
     * and a summary of how many companies are subscribed, on trial, or lapsed.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $status = (string) $request->query('status', 'all');
        if (! in_array($status, [...self::COMPANY_STATUSES, 'all'], true)) {
            $status = 'all';
        }

        $companies = Company::query()
            ->withCount('users')
            ->with('owner')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('platform.companies.index', [
            'companies' => $companies,
            'search' => $search,
            'status' => $status,
            'stats' => $this->summaryStats(),
        ]);
    }

    /** Read-only detail for one company: profile, team, and subscription history. */
    public function show(Company $company): View
    {
        $company->load([
            'owner',
            'users' => fn ($query) => $query->orderBy('name'),
            'subscriptions' => fn ($query) => $query->with('pricingPlan')->orderByDesc('id'),
        ]);

        return view('platform.companies.show', [
            'company' => $company,
        ]);
    }

    /**
     * Platform-wide counts for the summary cards.
     *
     * @return array{total:int, subscribed:int, trial:int, expired:int}
     */
    private function summaryStats(): array
    {
        $subscribed = Company::query()
            ->whereHas('subscriptions', fn ($query) => $this->scopeCurrentlyActive($query))
            ->count();

        $trial = Company::query()
            ->where('trial_ends_at', '>', now())
            ->whereDoesntHave('subscriptions', fn ($query) => $this->scopeCurrentlyActive($query))
            ->count();

        $total = Company::query()->count();

        return [
            'total' => $total,
            'subscribed' => $subscribed,
            'trial' => $trial,
            'expired' => max(0, $total - $subscribed - $trial),
        ];
    }

    /**
     * Constrain a subscriptions query to those granting access right now
     * (active status and not past their end date). Mirrors
     * Company::activeSubscription() so the counts and the per-row state agree.
     */
    private function scopeCurrentlyActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', Subscription::STATUS_ACTIVE)
            ->where(function ($inner): void {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }
}
