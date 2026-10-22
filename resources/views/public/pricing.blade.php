@extends('layouts.public')

@section('title', 'Construction Software Pricing')
@section('meta_description', 'Simple per-seat pricing in INR and USD. The whole Best CPMS platform is included — pick your number of users and term.')

@php
    use App\Support\Currency;

    // Active DISPLAY currency (shared sitewide by ShareDisplayCurrency), with a
    // safe fallback. Best CPMS sells worldwide, so prices are shown in the visitor's
    // currency and the other currency is listed alongside for reference.
    $active     = $currency ?? Currency::active();
    $all        = $currencies ?? Currency::all();
    $activeCode = $active['code'] ?? Currency::default();
    $perUser    = Currency::pricePerUser($active);
    $symbol     = $active['symbol'] ?? '';
    $locale     = $active['locale'] ?? 'en-US';

    $minSeats = (int) config('billing.min_seats');
    $seatOpts = config('billing.seat_options');
    $termOpts = config('billing.term_options');

    // The currency payments are actually taken in (INR via Razorpay). When the
    // visitor is viewing a display-only currency (e.g. USD), we show what they
    // will be billed so there are no surprises at checkout.
    $charge        = Currency::charge();
    $chargePerUser = Currency::pricePerUser($charge);
    $isDisplayOnly = Currency::isDisplayOnly($active);

    // Other configured currencies (shown as an "or … " reference line).
    $others = collect($all)->reject(fn ($c, $code) => $code === $activeCode);
@endphp

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Pricing',
        'title'  => 'One plan. Everything included.',
        'lead'   => 'No feature tiers, no per-project charges. You pay per user, per month — in your currency — and get planning, cost control, and reporting in full.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">

            {{-- Currency control — lets visitors flip the whole page between INR and USD. --}}
            @if (count($all) > 1)
                <div class="mb-10 flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-500">Show prices in</span>
                    @include('partials.currency-switcher', ['size' => 'md'])
                </div>
            @endif

            <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

                {{-- The offer --}}
                <div>
                    <p class="font-display text-5xl font-bold text-slate-900">{{ $symbol }}{{ number_format($perUser) }}<span class="text-lg font-medium text-slate-500"> / user / month</span></p>

                    @if ($others->isNotEmpty())
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            or {{ $others->map(fn ($c, $code) => ($c['symbol'] ?? '').number_format(Currency::pricePerUser($code)).' '.($c['code'] ?? $code))->implode(' · ') }} / user / month
                        </p>
                    @endif

                    <p class="mt-4 text-slate-600">Billed for the term you choose, for a minimum of {{ $minSeats }} users. Add seats whenever your team grows.</p>

                    <ul class="mt-8 space-y-3 text-slate-700">
                        @foreach ([
                            'Planning, scheduling & Gantt',
                            'BOQ, expenses & client payments',
                            'Project, cost & progress reports',
                            'Unlimited projects & tasks',
                            'Role-based access & tenant isolation',
                            'Mobile access for site teams',
                        ] as $item)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">✓</span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Live calculator --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-8">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Estimate your cost</h2>
                    <p class="mt-1 text-sm text-slate-500">The total is always {{ $symbol }}{{ number_format($perUser) }} × seats × months.</p>

                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Users</span>
                            <select id="seats" class="mt-1 w-full rounded-md border-slate-300 bg-white py-2.5 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($seatOpts as $s)
                                    <option value="{{ $s }}">{{ $s }} users</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Term</span>
                            <select id="months" class="mt-1 w-full rounded-md border-slate-300 bg-white py-2.5 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($termOpts as $m)
                                    <option value="{{ $m }}">{{ $m }} {{ \Illuminate\Support\Str::plural('month', $m) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="mt-6 rounded-lg border border-slate-200 bg-white p-5">
                        <p class="text-sm text-slate-500">Total for the term</p>
                        <p id="total" class="mt-1 font-display text-4xl font-bold text-slate-900">—</p>
                        <p id="breakdown" class="mt-1 text-sm text-slate-500"></p>
                        @if ($isDisplayOnly)
                            <p id="billed" class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-500"></p>
                        @endif
                    </div>

                    <a href="{{ route('register') }}" class="mt-6 block rounded-md bg-amber-500 px-6 py-3 text-center font-semibold text-white transition hover:bg-amber-600">Start free trial</a>
                    <p class="mt-3 text-center text-xs text-slate-500">Trial first — you're only billed when you subscribe.</p>
                </div>
            </div>

            <p class="mt-10 max-w-2xl text-sm text-slate-500">
                Prices shown in {{ $active['label'] ?? $activeCode }} ({{ $activeCode }}).
                @if ($isDisplayOnly)
                    Subscriptions are currently billed in {{ $charge['label'] ?? $charge['code'] }} ({{ $charge['code'] }}) at {{ $charge['symbol'] ?? '' }}{{ number_format($chargePerUser) }} / user / month via Razorpay.
                @endif
                Need more than {{ number_format(max($seatOpts)) }} seats or a custom term? <a href="{{ route('contact') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Talk to us</a>.
            </p>
        </div>
    </section>

    <script>
        (function () {
            var perUser   = {{ $perUser }};
            var symbol    = @json($symbol);
            var locale    = @json($locale);

            // Charge-currency figures, for the "billed as …" line on display-only
            // currencies. These mirror the server-side (authoritative) amount.
            var displayOnly   = @json($isDisplayOnly);
            var chargePerUser = {{ $chargePerUser }};
            var chargeSymbol  = @json($charge['symbol'] ?? '');
            var chargeLocale  = @json($charge['locale'] ?? 'en-IN');
            var chargeCode    = @json($charge['code'] ?? 'INR');

            var seats     = document.getElementById('seats');
            var months    = document.getElementById('months');
            var total     = document.getElementById('total');
            var breakdown = document.getElementById('breakdown');
            var billed    = document.getElementById('billed');

            function fmt(n, sym, loc) {
                try { return sym + n.toLocaleString(loc); }
                catch (e) { return sym + n; }
            }
            function recalc() {
                var s = parseInt(seats.value, 10);
                var m = parseInt(months.value, 10);
                total.textContent = fmt(perUser * s * m, symbol, locale);
                breakdown.textContent = s + ' users × ' + m + ' ' + (m === 1 ? 'month' : 'months') + ' × ' + fmt(perUser, symbol, locale);
                if (displayOnly && billed) {
                    billed.textContent = 'Billed as ' + fmt(chargePerUser * s * m, chargeSymbol, chargeLocale) + ' ' + chargeCode + ' via Razorpay.';
                }
            }
            seats.addEventListener('change', recalc);
            months.addEventListener('change', recalc);
            recalc();
        })();
    </script>

@endsection
