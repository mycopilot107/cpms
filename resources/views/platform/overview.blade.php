@extends('layouts.app')

@section('title', 'Platform overview')
@section('heading', 'Platform overview')

@php
    $money = fn ($row) => \App\Support\Currency::format((int) $row->total, $row->currency).' '.$row->currency;
    $cards = [
        ['Companies', $stats['companies'], '+'.$stats['new_companies'].' in last 30 days', route('platform.companies.index'), 'text-slate-900'],
        ['Subscribed', $stats['subscribed'], 'paying companies', route('platform.companies.index'), 'text-emerald-700'],
        ['On trial', $stats['on_trial'], 'free trial running', route('platform.companies.index'), 'text-indigo-700'],
        ['Users', $stats['users'], 'across all companies', route('platform.users.index'), 'text-slate-900'],
        ['Suspended / inactive', $stats['suspended'], 'companies blocked', route('platform.companies.index', ['status' => 'suspended']), 'text-rose-700'],
        ['Failed payments', $stats['failed_payments'], 'in last 30 days', route('platform.payments.index', ['status' => 'failed']), 'text-rose-700'],
        ['Unread messages', $stats['unread_messages'], 'contact and feedback', route('platform.messages.index', ['type' => 'unread']), 'text-amber-700'],
    ];
@endphp

@section('content')
<div class="space-y-6">
    {{-- Revenue --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Revenue this month</p>
            @forelse ($revenueThisMonth as $row)
                <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $money($row) }}</p>
                <p class="text-xs text-emerald-700">{{ $row->payments }} paid order(s)</p>
            @empty
                <p class="mt-1 text-2xl font-bold text-emerald-800">—</p>
                <p class="text-xs text-emerald-700">No paid orders yet this month</p>
            @endforelse
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Revenue all time</p>
            @forelse ($revenueAllTime as $row)
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $money($row) }}</p>
                <p class="text-xs text-slate-500">{{ $row->payments }} paid order(s)</p>
            @empty
                <p class="mt-1 text-2xl font-bold text-slate-900">—</p>
                <p class="text-xs text-slate-500">No paid orders yet</p>
            @endforelse
        </div>
    </div>

    {{-- Counts --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
        @foreach ($cards as [$label, $value, $hint, $url, $tone])
            <a href="{{ $url }}" class="rounded-xl border border-slate-200 bg-white p-4 transition hover:border-indigo-300 hover:shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-bold {{ $tone }}">{{ $value }}</p>
                <p class="mt-0.5 text-xs text-slate-400">{{ $hint }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Recent payments --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h2 class="font-semibold text-slate-900">Recent payments</h2>
                <a href="{{ route('platform.payments.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">All payments</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentPayments as $payment)
                    <a href="{{ route('platform.payments.show', $payment->id) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-800">{{ $payment->company?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $payment->created_at?->format('d M Y, H:i') }} · {{ $payment->seats }} users · {{ $payment->months }} mo</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="font-semibold text-slate-800">{{ \App\Support\Currency::format($payment->amount, $payment->currency) }}</span>
                            @include('platform.partials.payment-status', ['status' => $payment->status])
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">No payments yet.</p>
                @endforelse
            </div>
        </section>

        {{-- New companies --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h2 class="font-semibold text-slate-900">New companies</h2>
                <a href="{{ route('platform.companies.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Add company</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($newCompanies as $company)
                    <a href="{{ route('platform.companies.show', $company) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-800">{{ $company->name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $company->owner?->email ?? $company->email }} · {{ $company->country }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-500">{{ $company->created_at?->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">No companies yet.</p>
                @endforelse
            </div>
        </section>

        {{-- Renewals --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h2 class="font-semibold text-slate-900">Subscriptions ending in 30 days</h2>
                <p class="text-xs text-slate-500">Follow up so they renew.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($renewals as $subscription)
                    <a href="{{ route('platform.companies.show', $subscription->company_id) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                        <span class="truncate font-medium text-slate-800">{{ $subscription->company?->name ?? '—' }}</span>
                        <span class="shrink-0 text-xs text-amber-700">{{ $subscription->ends_at?->format('d M Y') }} · {{ $subscription->ends_at?->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">No subscriptions end in the next 30 days.</p>
                @endforelse
            </div>
        </section>

        {{-- Trials ending --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h2 class="font-semibold text-slate-900">Trials ending in 7 days</h2>
                <p class="text-xs text-slate-500">Good time to offer help or extend the trial.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($trialsEnding as $company)
                    <a href="{{ route('platform.companies.show', $company) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                        <span class="truncate font-medium text-slate-800">{{ $company->name }}</span>
                        <span class="shrink-0 text-xs text-indigo-700">{{ $company->trial_ends_at?->format('d M Y') }} · {{ $company->trial_ends_at?->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">No trials end in the next 7 days.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
