@extends('layouts.app')

@section('title', $company->name . ' — platform')

@section('content')
@php
    $stateBadge = [
        \App\Models\Company::SUB_STATE_SUBSCRIBED => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        \App\Models\Company::SUB_STATE_TRIAL      => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        \App\Models\Company::SUB_STATE_EXPIRED    => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];
    $stateLabel = [
        \App\Models\Company::SUB_STATE_SUBSCRIBED => 'Subscribed',
        \App\Models\Company::SUB_STATE_TRIAL      => 'On trial',
        \App\Models\Company::SUB_STATE_EXPIRED    => 'Expired',
    ];
    $subBadge = [
        \App\Models\Subscription::STATUS_ACTIVE    => 'bg-emerald-50 text-emerald-700',
        \App\Models\Subscription::STATUS_TRIAL     => 'bg-indigo-50 text-indigo-700',
        \App\Models\Subscription::STATUS_EXPIRED   => 'bg-rose-50 text-rose-700',
        \App\Models\Subscription::STATUS_CANCELLED => 'bg-slate-100 text-slate-600',
    ];
    $state = $company->subscriptionState();
    $expiry = $company->accessEndsAt();
@endphp

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-0">

    <a href="{{ route('platform.companies.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">&larr; All companies</a>

    <header class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900">{{ $company->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Registered {{ $company->created_at?->format('j M Y') ?? '—' }}</p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $stateBadge[$state] }}">{{ $stateLabel[$state] }}</span>
            @if ($expiry)
                <p class="text-sm {{ $expiry->isPast() ? 'text-rose-600' : 'text-slate-600' }}">
                    {{ $expiry->isPast() ? 'Expired' : 'Access until' }} {{ $expiry->format('j M Y') }} ({{ $expiry->diffForHumans() }})
                </p>
            @endif
        </div>
    </header>

    {{-- Company profile --}}
    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Company</h2>
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
            <div><dt class="text-xs text-slate-400">Status</dt><dd class="text-slate-800">{{ ucfirst($company->status) }}</dd></div>
            <div><dt class="text-xs text-slate-400">Owner</dt><dd class="text-slate-800">{{ $company->owner?->name ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Email</dt><dd class="text-slate-800">{{ $company->email ?: '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Phone</dt><dd class="text-slate-800">{{ $company->phone ?: '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Location</dt><dd class="text-slate-800">{{ collect([$company->city, $company->state, $company->country])->filter()->join(', ') ?: '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">GST</dt><dd class="text-slate-800">{{ $company->gst_number ?: '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Trial ends</dt><dd class="text-slate-800">{{ $company->trial_ends_at?->format('j M Y') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-400">Users</dt><dd class="text-slate-800">{{ $company->users->count() }}@if ($company->seatLimit() !== null) / {{ $company->seatLimit() }} seats @endif</dd></div>
        </dl>
    </section>

    {{-- Subscription history --}}
    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Subscription history</h2>
        @if ($company->subscriptions->isEmpty())
            <p class="text-sm text-slate-500">No subscriptions on record — this company has only ever been on the free trial.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="py-2 pr-4">Plan</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Seats</th>
                            <th class="py-2 pr-4">Term</th>
                            <th class="py-2 pr-4">Amount</th>
                            <th class="py-2 pr-4">Starts</th>
                            <th class="py-2 pr-4">Ends</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($company->subscriptions as $sub)
                            <tr>
                                <td class="py-2 pr-4 font-medium text-slate-800">{{ $sub->pricingPlan?->name ?? ucfirst($sub->plan) }}</td>
                                <td class="py-2 pr-4"><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $subBadge[$sub->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($sub->status) }}</span></td>
                                <td class="py-2 pr-4 text-slate-600">{{ $sub->seats ?? '—' }}</td>
                                <td class="py-2 pr-4 text-slate-600">{{ $sub->billing_months ? $sub->billing_months . ' mo' : '—' }}</td>
                                <td class="py-2 pr-4 text-slate-600">{{ $sub->amount ? $sub->currency . ' ' . number_format($sub->amount) : '—' }}</td>
                                <td class="py-2 pr-4 text-slate-600">{{ $sub->starts_at?->format('j M Y') ?? '—' }}</td>
                                <td class="py-2 pr-4 text-slate-600">{{ $sub->ends_at?->format('j M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Team --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Team ({{ $company->users->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Role</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($company->users as $member)
                        <tr>
                            <td class="py-2 pr-4 font-medium text-slate-800">{{ $member->name }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $member->role?->name }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $member->email }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ ucfirst($member->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
