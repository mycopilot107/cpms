@extends('layouts.app')

@section('title', 'Platform — companies')

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
    $statusChip = [
        \App\Models\Company::STATUS_ACTIVE    => 'bg-slate-100 text-slate-600',
        \App\Models\Company::STATUS_SUSPENDED => 'bg-amber-100 text-amber-700',
        \App\Models\Company::STATUS_INACTIVE  => 'bg-slate-200 text-slate-500',
    ];
@endphp

<div class="mx-auto max-w-6xl px-4 py-8 sm:px-0">

    <header class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900">Platform console</h1>
        <p class="mt-1 text-sm text-slate-600">Every company on Best CPMS and when its access expires. Read-only.</p>
    </header>

    {{-- Summary --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Companies</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Subscribed</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['subscribed'] }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">On trial</p>
            <p class="mt-1 text-2xl font-bold text-indigo-700">{{ $stats['trial'] }}</p>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-rose-700">Expired</p>
            <p class="mt-1 text-2xl font-bold text-rose-700">{{ $stats['expired'] }}</p>
        </div>
    </div>

    {{-- Search + status filter --}}
    <form method="GET" action="{{ route('platform.companies.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative grow sm:grow-0">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search name or email…"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-72">
        </div>
        <select name="status" onchange="this.form.submit()"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="all" @selected($status === 'all')>All statuses</option>
            <option value="{{ \App\Models\Company::STATUS_ACTIVE }}" @selected($status === \App\Models\Company::STATUS_ACTIVE)>Active</option>
            <option value="{{ \App\Models\Company::STATUS_SUSPENDED }}" @selected($status === \App\Models\Company::STATUS_SUSPENDED)>Suspended</option>
            <option value="{{ \App\Models\Company::STATUS_INACTIVE }}" @selected($status === \App\Models\Company::STATUS_INACTIVE)>Inactive</option>
        </select>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">Search</button>
        @if ($search !== '' || $status !== 'all')
            <a href="{{ route('platform.companies.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Clear</a>
        @endif
    </form>

    @if ($companies->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <p class="font-medium text-slate-700">No companies match.</p>
            <p class="mt-1 text-sm text-slate-500">Try a different search or clear the filters.</p>
        </div>
    @else
        {{-- Desktop table --}}
        <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Company</th>
                        <th class="px-4 py-3">Owner</th>
                        <th class="px-4 py-3">Users</th>
                        <th class="px-4 py-3">Access</th>
                        <th class="px-4 py-3">Expiry</th>
                        <th class="px-4 py-3"><span class="sr-only">View</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($companies as $company)
                        @php
                            $state = $company->subscriptionState();
                            $expiry = $company->accessEndsAt();
                            $limit = $company->seatLimit();
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('platform.companies.show', $company) }}" class="font-semibold text-slate-900 hover:text-indigo-600">{{ $company->name }}</a>
                                <p class="text-xs text-slate-500">{{ $company->email ?: '—' }}</p>
                                <span class="mt-1 inline-block rounded px-1.5 py-0.5 text-[11px] font-medium {{ $statusChip[$company->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($company->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $company->owner?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $company->users_count }}@if ($limit !== null)<span class="text-slate-400"> / {{ $limit }}</span>@endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $stateBadge[$state] }}">{{ $stateLabel[$state] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($expiry)
                                    <span class="font-medium text-slate-800">{{ $expiry->format('j M Y') }}</span>
                                    <p class="text-xs {{ $expiry->isPast() ? 'text-rose-600' : 'text-slate-500' }}">{{ $expiry->diffForHumans() }}</p>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('platform.companies.show', $company) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @foreach ($companies as $company)
                @php
                    $state = $company->subscriptionState();
                    $expiry = $company->accessEndsAt();
                    $limit = $company->seatLimit();
                @endphp
                <a href="{{ route('platform.companies.show', $company) }}" class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $company->name }}</p>
                            <p class="text-xs text-slate-500">{{ $company->email ?: '—' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $stateBadge[$state] }}">{{ $stateLabel[$state] }}</span>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-xs text-slate-400">Owner</dt>
                            <dd class="text-slate-700">{{ $company->owner?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Users</dt>
                            <dd class="text-slate-700">{{ $company->users_count }}@if ($limit !== null) / {{ $limit }}@endif</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-slate-400">Expiry</dt>
                            <dd class="text-slate-700">
                                @if ($expiry)
                                    {{ $expiry->format('j M Y') }} <span class="{{ $expiry->isPast() ? 'text-rose-600' : 'text-slate-500' }}">· {{ $expiry->diffForHumans() }}</span>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $companies->links() }}</div>
    @endif

</div>
@endsection
