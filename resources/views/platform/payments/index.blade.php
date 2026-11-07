@extends('layouts.app')

@section('title', 'Payments — platform')
@section('heading', 'Payments')

@section('actions')
    <a href="{{ route('platform.payments.index', array_filter($filters + ['export' => 'csv'], fn ($v) => $v !== null && $v !== '' && $v !== 'all')) }}"
       class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Export CSV</a>
@endsection

@php
    $inputClass = 'rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $statusLabels = ['paid' => 'Paid', 'created' => 'Not completed', 'failed' => 'Failed'];
@endphp

@section('content')
<div class="space-y-6">
    {{-- Totals for the current filter --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Collected (filtered)</p>
            @forelse ($totals as $row)
                <p class="mt-1 text-xl font-bold text-emerald-800">{{ \App\Support\Currency::format((int) $row->total, $row->currency) }} {{ $row->currency }}</p>
            @empty
                <p class="mt-1 text-xl font-bold text-emerald-800">—</p>
            @endforelse
        </div>
        @foreach ($statusLabels as $status => $label)
            <a href="{{ route('platform.payments.index', ['status' => $status]) }}" class="rounded-xl border border-slate-200 bg-white p-4 hover:border-indigo-300">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ $counts[$status] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('platform.payments.index') }}" class="flex flex-wrap items-end gap-3">
        <label class="block">
            <span class="text-xs font-medium text-slate-500">Search</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Company, receipt, Razorpay ID…" class="{{ $inputClass }} mt-1 block w-72">
        </label>
        <label class="block">
            <span class="text-xs font-medium text-slate-500">Status</span>
            <select name="status" class="{{ $inputClass }} mt-1 block">
                <option value="all">All</option>
                @foreach ($statusLabels as $status => $label)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="text-xs font-medium text-slate-500">From</span>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="{{ $inputClass }} mt-1 block">
        </label>
        <label class="block">
            <span class="text-xs font-medium text-slate-500">To</span>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="{{ $inputClass }} mt-1 block">
        </label>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
        @if ($filters['q'] !== '' || $filters['status'] !== 'all' || $filters['from'] || $filters['to'])
            <a href="{{ route('platform.payments.index') }}" class="py-2 text-sm font-medium text-slate-500 hover:text-slate-700">Clear</a>
        @endif
    </form>

    {{-- List --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Company</th>
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Razorpay payment</th>
                        <th class="px-4 py-3"><span class="sr-only">Details</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->created_at?->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">
                                @if ($payment->company)
                                    <a href="{{ route('platform.companies.show', $payment->company) }}" class="font-medium text-slate-900 hover:text-indigo-600">{{ $payment->company->name }}</a>
                                @else
                                    <span class="text-slate-400">Deleted company</span>
                                @endif
                                <p class="text-xs text-slate-400">{{ $payment->user?->email ?? '—' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->seats }} users · {{ $payment->months }} mo</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">{{ \App\Support\Currency::format($payment->amount, $payment->currency) }} <span class="text-xs font-normal text-slate-400">{{ $payment->currency }}</span></td>
                            <td class="px-4 py-3">
                                @include('platform.partials.payment-status', ['status' => $payment->status])
                                @if ($payment->failure_reason)
                                    <p class="mt-1 max-w-[200px] truncate text-xs text-rose-600" title="{{ $payment->failure_reason }}">{{ $payment->failure_reason }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $payment->razorpay_payment_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('platform.payments.show', $payment->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">No payments match.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
