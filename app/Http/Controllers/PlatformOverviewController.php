<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\View\View;

/** Platform landing page: headline numbers and what needs attention. */
class PlatformOverviewController extends Controller
{
    public function index(): View
    {
        $monthStart = now()->startOfMonth();

        $revenue = fn ($query) => $query
            ->where('status', Payment::STATUS_PAID)
            ->selectRaw('currency, SUM(amount) as total, COUNT(*) as payments')
            ->groupBy('currency')
            ->get();

        return view('platform.overview', [
            'stats' => [
                'companies' => Company::query()->count(),
                'new_companies' => Company::query()->where('created_at', '>=', now()->subDays(30))->count(),
                'users' => User::query()->count(),
                'subscribed' => Company::query()->whereHas('subscriptions', fn ($q) => $q
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->where(fn ($inner) => $inner->whereNull('ends_at')->orWhere('ends_at', '>', now())))
                    ->count(),
                'on_trial' => Company::query()->where('trial_ends_at', '>', now())->count(),
                'suspended' => Company::query()->where('status', '!=', Company::STATUS_ACTIVE)->count(),
                'failed_payments' => Payment::acrossCompanies()->where('status', Payment::STATUS_FAILED)->where('created_at', '>=', now()->subDays(30))->count(),
                'unread_messages' => ContactMessage::query()->whereNull('read_at')->count(),
            ],
            'revenueThisMonth' => $revenue(Payment::acrossCompanies()->where('updated_at', '>=', $monthStart)),
            'revenueAllTime' => $revenue(Payment::acrossCompanies()),
            'recentPayments' => Payment::acrossCompanies()->with('company')->latest()->limit(6)->get(),
            'newCompanies' => Company::query()->with('owner')->latest()->limit(6)->get(),
            'renewals' => Subscription::query()
                ->with('company')
                ->where('status', Subscription::STATUS_ACTIVE)
                ->whereBetween('ends_at', [now(), now()->addDays(30)])
                ->orderBy('ends_at')
                ->limit(10)
                ->get(),
            'trialsEnding' => Company::query()
                ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
                ->whereDoesntHave('subscriptions', fn ($q) => $q->where('status', Subscription::STATUS_ACTIVE))
                ->orderBy('trial_ends_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
