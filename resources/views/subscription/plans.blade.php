@extends('layouts.app')

@section('title', 'Choose a plan — Best CPMS')
@section('heading', 'Choose your plan')

@php
    // Default the seat selector to the smallest option that covers both the
    // minimum and the company's current headcount.
    $requiredSeats = max($minSeats, $usedSeats);
    $defaultSeats = $minSeats;
    foreach ($seatOptions as $opt) {
        if ($opt >= $requiredSeats) { $defaultSeats = $opt; break; }
        $defaultSeats = $opt;
    }
    $defaultMonths = old('months', $termOptions[0] ?? 1);
    $defaultSeats = old('seats', $defaultSeats);
@endphp

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">

        @if (session('error'))
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($activeSubscription)
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                You have an active plan: <span class="font-semibold">{{ $activeSubscription->seats }} users</span>,
                valid until <span class="font-semibold">{{ optional($activeSubscription->ends_at)->format('d M Y') }}</span>.
                Buying again adds the new months onto that date and updates the seat count immediately.
            </div>
        @endif

        <div class="rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-semibold text-slate-900">Subscribe {{ $company->name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $currencySymbol }}{{ number_format($pricePerUser) }} per user, per {{ $billingPeriod }}. Minimum {{ $minSeats }} users.
                    You currently have <span class="font-medium text-slate-700">{{ $usedSeats }}</span> user(s).
                </p>
                @unless ($displayIsCharge)
                    <p class="text-xs text-slate-400">or {{ $displayCurrency['symbol'] ?? '' }}{{ number_format($displayPricePerUser) }} {{ $displayCurrency['code'] ?? '' }} per user, per {{ $billingPeriod }} (reference — billed in {{ $chargeCurrency['code'] }}).</p>
                @endunless
            </div>

            @unless ($gatewayReady)
                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Online payment isn't configured yet. Ask your administrator to add the Razorpay keys
                    (<code>RAZORPAY_KEY</code> / <code>RAZORPAY_SECRET</code>) before purchasing.
                </div>
            @endunless

            <form method="POST" action="{{ route('subscription.checkout') }}" class="mt-6 space-y-6"
                  data-price-per-user="{{ $pricePerUser }}">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="seats" class="block text-sm font-medium text-slate-700">Number of users</label>
                        <select id="seats" name="seats"
                                class="mt-1 block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($seatOptions as $opt)
                                <option value="{{ $opt }}" @selected((int) $defaultSeats === (int) $opt)>{{ $opt }} users</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="months" class="block text-sm font-medium text-slate-700">Duration</label>
                        <select id="months" name="months"
                                class="mt-1 block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($termOptions as $m)
                                <option value="{{ $m }}" @selected((int) $defaultMonths === (int) $m)>{{ $m }} {{ $m == 1 ? 'month' : 'months' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-lg bg-slate-50 p-4">
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span id="calc-line">—</span>
                    </div>
                    <div class="mt-1 flex items-baseline justify-between">
                        <span class="text-sm font-medium text-slate-700">Total</span>
                        <span id="total-amount" class="text-2xl font-bold text-slate-900">{{ $currencySymbol }}0</span>
                    </div>
                    @unless ($displayIsCharge)
                        <div class="mt-1 flex items-baseline justify-between">
                            <span class="text-xs text-slate-400">Reference</span>
                            <span id="total-ref" class="text-sm font-medium text-slate-500"></span>
                        </div>
                        <p class="mt-2 border-t border-slate-200 pt-2 text-xs text-slate-400">
                            Billed in {{ $chargeCurrency['label'] ?? $chargeCurrency['code'] }} ({{ $chargeCurrency['code'] }}) via Razorpay — {{ $displayCurrency['code'] }} is shown for reference only.
                        </p>
                    @endunless
                </div>

                <button type="submit" @disabled(! $gatewayReady)
                        class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-indigo-600/50 sm:w-auto">
                    Proceed to payment
                </button>
            </form>
        </div>

        @if ($payments->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-6 py-3">
                    <h2 class="font-semibold text-slate-900">Payment history</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($payments as $payment)
                        <div class="flex items-center justify-between gap-4 px-6 py-3 text-sm">
                            <div>
                                <div class="font-medium text-slate-800">
                                    {{ $currencySymbol }}{{ number_format($payment->amount) }}
                                    · {{ $payment->seats }} users · {{ $payment->months }} {{ $payment->months == 1 ? 'month' : 'months' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $payment->created_at->format('d M Y, H:i') }}
                                    @if ($payment->receipt) · {{ $payment->receipt }} @endif
                                </div>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $payment->status === 'paid' ? 'bg-green-50 text-green-700' : ($payment->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-slate-500 underline hover:text-slate-700">Log out</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var form = document.querySelector('form[data-price-per-user]');
            if (!form) return;
            var pricePerUser = parseInt(form.getAttribute('data-price-per-user'), 10) || 0;
            var seatsEl = document.getElementById('seats');
            var monthsEl = document.getElementById('months');
            var totalEl = document.getElementById('total-amount');
            var lineEl = document.getElementById('calc-line');
            var fmt = new Intl.NumberFormat('en-IN');
            var symbol = @json($currencySymbol);

            // Display-currency reference (shown only when it isn't the charge
            // currency). This is a reference figure — the charged amount is the
            // INR total above and is recomputed server-side at checkout.
            var displayIsCharge  = @json($displayIsCharge);
            var dispPricePerUser = @json((int) $displayPricePerUser);
            var dispSymbol       = @json($displayCurrency['symbol'] ?? '');
            var dispLocale       = @json($displayCurrency['locale'] ?? 'en-US');
            var dispCode         = @json($displayCurrency['code'] ?? '');
            var totalRefEl       = document.getElementById('total-ref');
            var dispFmt;
            try { dispFmt = new Intl.NumberFormat(dispLocale); }
            catch (e) { dispFmt = { format: function (n) { return n; } }; }

            function recalc() {
                var seats = parseInt(seatsEl.value, 10) || 0;
                var months = parseInt(monthsEl.value, 10) || 0;
                var total = seats * months * pricePerUser;
                totalEl.textContent = symbol + fmt.format(total);
                lineEl.textContent = seats + ' users × ' + months + ' month(s) × ' + symbol + fmt.format(pricePerUser);
                if (!displayIsCharge && totalRefEl) {
                    totalRefEl.textContent = dispSymbol + dispFmt.format(seats * months * dispPricePerUser) + ' ' + dispCode;
                }
            }

            seatsEl.addEventListener('change', recalc);
            monthsEl.addEventListener('change', recalc);
            recalc();
        })();
    </script>
@endsection
