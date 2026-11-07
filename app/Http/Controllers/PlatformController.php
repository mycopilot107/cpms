<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlatformCompanyRequest;
use App\Http\Requests\UpdatePlatformCompanyRequest;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\CompanyProvisioner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Platform console for super admins (see config/platform.php).
 *
 * This is the one place in the app that deliberately crosses the tenant
 * boundary: it lists EVERY company and its subscription / trial expiry, and
 * lets a super admin add a company or edit its profile, status, and trial
 * end date. Access is restricted by the `super-admin` middleware.
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

    public function create(): View
    {
        return view('platform.companies.create', ['standardTrialEnds' => Company::trialEndsAt(now())]);
    }

    /** Add a company with its owner login and a trial, like a self sign-up. */
    public function store(StorePlatformCompanyRequest $request, CompanyProvisioner $provisioner): RedirectResponse
    {
        $data = $request->validated();

        $owner = $provisioner->provision(
            company: collect($data)->only(['name', 'email', 'phone', 'country', 'currency', 'address', 'city', 'state', 'gst_number'])->all(),
            owner: [
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => $data['owner_password'],
            ],
            trialDays: isset($data['trial_days']) ? (int) $data['trial_days'] : null,
        );

        return redirect()
            ->route('platform.companies.show', $owner->company_id)
            ->with('status', 'Company added. The owner can sign in with '.$owner->email.'.');
    }

    public function edit(Company $company): View
    {
        return view('platform.companies.edit', ['company' => $company]);
    }

    /** Edit profile, status, and trial end date. Suspended/inactive blocks access. */
    public function update(UpdatePlatformCompanyRequest $request, Company $company): RedirectResponse
    {
        $data = $request->validated();
        $trialEndsAt = isset($data['trial_ends_at']) ? Carbon::parse($data['trial_ends_at'])->endOfDay() : null;

        DB::transaction(function () use ($company, $data, $trialEndsAt): void {
            $company->update(collect($data)->except('trial_ends_at')->all() + ['trial_ends_at' => $trialEndsAt]);

            $company->subscriptions()
                ->where('status', Subscription::STATUS_TRIAL)
                ->update(['trial_ends_at' => $trialEndsAt]);
        });

        return redirect()
            ->route('platform.companies.show', $company)
            ->with('status', 'Company updated.');
    }

    /** Read-only detail for one company: profile, team, and subscription history. */
    public function show(Company $company): View
    {
        $company->load([
            'owner',
            'users' => fn ($query) => $query->with('role')->orderBy('name'),
            'subscriptions' => fn ($query) => $query->with('pricingPlan')->orderByDesc('id'),
        ]);

        return view('platform.companies.show', [
            'company' => $company,
            'payments' => Payment::acrossCompanies()
                ->with('user')
                ->where('company_id', $company->id)
                ->latest()
                ->get(),
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
    private function scopeCurrentlyActive(Builder $query): void
    {
        $query->where('status', Subscription::STATUS_ACTIVE)
            ->where(function ($inner): void {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }
}
