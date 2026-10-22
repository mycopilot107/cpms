@extends('layouts.app')

@section('title', 'Complete payment — Best CPMS')
@section('heading', 'Complete your payment')

@section('content')
    <div class="mx-auto max-w-lg space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-900">Order summary</h2>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Company</dt>
                    <dd class="font-medium text-slate-800">{{ $company->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Users</dt>
                    <dd class="font-medium text-slate-800">{{ $payment->seats }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Duration</dt>
                    <dd class="font-medium text-slate-800">{{ $payment->months }} {{ $payment->months == 1 ? 'month' : 'months' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Rate</dt>
                    <dd class="font-medium text-slate-800">{{ $currencySymbol }}{{ number_format($payment->price_per_user) }} / user / {{ $billingPeriod }}</dd>
                </div>
                <div class="mt-2 flex justify-between border-t border-slate-200 pt-3 text-base">
                    <dt class="font-semibold text-slate-700">Total</dt>
                    <dd class="font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($payment->amount) }}</dd>
                </div>
                @unless ($displayIsCharge)
                    <div class="flex justify-between text-xs text-slate-400">
                        <dt>Reference ({{ $displayCurrency['code'] }})</dt>
                        <dd>{{ $displayCurrency['symbol'] ?? '' }}{{ number_format($displayAmount) }} {{ $displayCurrency['code'] }}</dd>
                    </div>
                @endunless
            </dl>

            @unless ($displayIsCharge)
                <p class="mt-4 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                    You'll be charged in {{ $chargeCurrency['label'] ?? $chargeCurrency['code'] }} ({{ $chargeCurrency['code'] }}) via Razorpay. The {{ $displayCurrency['code'] }} figure is for reference only.
                </p>
            @endunless

            <button id="pay-button"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Pay {{ $currencySymbol }}{{ number_format($payment->amount) }} now
            </button>

            <p id="pay-help" class="mt-3 text-center text-xs text-slate-400">
                A secure Razorpay window will open. After a successful charge, this page confirms the plan. If you close the window, the payment is still checked with Razorpay and the plan is activated.
            </p>

            <p class="mt-4 text-center">
                <a href="{{ route('subscription.plans') }}" class="text-sm text-slate-500 underline hover:text-slate-700">Change plan</a>
            </p>
        </div>
    </div>

    {{-- Verification form: the Checkout handler fills these and submits. --}}
    <form id="rzp-verify" method="POST" action="{{ route('subscription.verify') }}" class="hidden">
        @csrf
        <input type="hidden" name="razorpay_order_id" value="{{ $order['id'] }}">
        <input type="hidden" name="razorpay_payment_id" value="">
        <input type="hidden" name="razorpay_signature" value="">
    </form>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        (function () {
            var form = document.getElementById('rzp-verify');
            var help = document.getElementById('pay-help');
            var payBtn = document.getElementById('pay-button');

            var options = {
                key: @json($razorpayKey),
                amount: @json((int) $order['amount']),      // in paise, from the created order
                currency: @json($order['currency']),
                name: 'Best CPMS',
                description: @json($payment->seats.' users · '.$payment->months.' month(s)'),
                order_id: @json($order['id']),
                prefill: {
                    name: @json($user->name),
                    email: @json($user->email),
                },
                notes: { receipt: @json($payment->receipt) },
                theme: { color: '#4f46e5' },
                handler: function (response) {
                    form.querySelector('input[name="razorpay_payment_id"]').value = response.razorpay_payment_id || '';
                    form.querySelector('input[name="razorpay_signature"]').value = response.razorpay_signature || '';
                    // razorpay_order_id is already set from the server-created order.
                    if (response.razorpay_order_id) {
                        form.querySelector('input[name="razorpay_order_id"]').value = response.razorpay_order_id;
                    }
                    help.textContent = 'Verifying your payment…';
                    form.submit();
                },
                modal: {
                    ondismiss: function () {
                        help.textContent = 'Payment window closed. You can pay again with the button above.';
                    }
                }
            };

            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function (resp) {
                help.textContent = 'Payment failed: ' + ((resp.error && resp.error.description) || 'please try again.');
            });

            function open() { rzp.open(); }
            payBtn.addEventListener('click', open);

            // Auto-open once on load (button remains for retries / popup blockers).
            window.addEventListener('load', open);
        })();
    </script>
@endsection
