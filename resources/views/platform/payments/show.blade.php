@extends('layouts.app')

@section('title', 'Payment '.$payment->receipt.' — platform')
@section('heading', 'Payment details')

@php
    $rows = [
        'Receipt' => $payment->receipt,
        'Date' => $payment->created_at?->format('d M Y, H:i:s'),
        'Last updated' => $payment->updated_at?->format('d M Y, H:i:s'),
        'Paid by' => $payment->user ? $payment->user->name.' ('.$payment->user->email.')' : '—',
        'Plan' => $payment->plan?->name ?? '—',
        'Users (seats)' => $payment->seats,
        'Term' => $payment->months.' '.\Illuminate\Support\Str::plural('month', $payment->months).($payment->billing_period ? ' · billed per '.$payment->billing_period : ''),
        'Price per user' => \App\Support\Currency::format($payment->price_per_user, $payment->currency).' '.$payment->currency,
        'Razorpay order ID' => $payment->razorpay_order_id ?? '—',
        'Razorpay payment ID' => $payment->razorpay_payment_id ?? '—',
    ];
@endphp

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('platform.payments.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">&larr; All payments</a>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">
                    @if ($payment->company)
                        <a href="{{ route('platform.companies.show', $payment->company) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $payment->company->name }}</a>
                    @else
                        Deleted company
                    @endif
                </p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ \App\Support\Currency::format($payment->amount, $payment->currency) }} <span class="text-base font-medium text-slate-400">{{ $payment->currency }}</span></p>
            </div>
            @include('platform.partials.payment-status', ['status' => $payment->status])
        </div>

        @if ($payment->failure_reason)
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <span class="font-semibold">Failure reason:</span> {{ $payment->failure_reason }}
            </div>
        @endif

        <dl class="mt-6 divide-y divide-slate-100 text-sm">
            @foreach ($rows as $label => $value)
                <div class="flex flex-wrap justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">{{ $label }}</dt>
                    <dd class="break-all text-right font-medium text-slate-800">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($payment->razorpay_payment_id)
            <a href="https://dashboard.razorpay.com/app/payments/{{ urlencode($payment->razorpay_payment_id) }}" target="_blank" rel="noopener"
               class="mt-6 inline-flex rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Open in Razorpay dashboard</a>
        @endif
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-3 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Subscription this payment activated</h2>
        @if ($payment->subscription)
            <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div><dt class="text-xs text-slate-400">Status</dt><dd class="font-medium text-slate-800">{{ ucfirst($payment->subscription->status) }}</dd></div>
                <div><dt class="text-xs text-slate-400">Seats</dt><dd class="font-medium text-slate-800">{{ $payment->subscription->seats ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Starts</dt><dd class="font-medium text-slate-800">{{ $payment->subscription->starts_at?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Ends</dt><dd class="font-medium text-slate-800">{{ $payment->subscription->ends_at?->format('d M Y') ?? '—' }}</dd></div>
            </dl>
        @else
            <p class="text-sm text-slate-500">None — the payment was not completed, so no plan was activated.</p>
        @endif
    </section>
</div>
@endsection
