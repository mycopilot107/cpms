{{--
    Keyword "Solutions" landing page. One template renders every entry in
    config/landing.php; the LandingController picks the entry from the matched
    route name. House style matches the pillar page (cpm-software) and the
    services pages: blueprint-grid hero, amber primary CTA, indigo accents,
    rounded-xl bordered cards, font-display headings.

    Expects:
      $key   string  config key (e.g. "contractors")
      $page  array   the config/landing.php entry
--}}
@extends('layouts.public')

@php
    use App\Support\Currency;

    // --- Pricing: single source of truth is config/billing.php. Config text
    //     uses tokens (:symbol, :perUser, …) which we substitute here so the
    //     landing config stays cacheable and pricing never drifts out of sync.
    //     Tokens reflect the ACTIVE display currency (Best CPMS sells worldwide), so
    //     the prose reads in the visitor's currency.
    $active     = $currency ?? Currency::active();
    $all        = $currencies ?? Currency::all();
    $activeCode = $active['code'] ?? Currency::default();
    $symbol     = $active['symbol'] ?? '₹';
    $perUser    = Currency::pricePerUser($active);
    $minSeats   = (int) config('billing.min_seats', 10);
    $fromMonth  = $perUser * $minSeats;
    $trialDays  = \App\Models\Company::TRIAL_DAYS;

    $tokens = [
        ':symbol'    => $symbol,
        ':perUser'   => number_format($perUser),
        ':minSeats'  => (string) $minSeats,
        ':fromMonth' => number_format($fromMonth),
        ':trialDays' => (string) $trialDays,
    ];
    // Token-substitute a string (null-safe).
    $fill = fn (?string $s) => $s === null ? '' : strtr($s, $tokens);

    // Dual-currency "from" phrase for the hero chip: active first, the other
    // currency(ies) in parentheses — so both INR and USD are visible.
    $others     = collect($all)->reject(fn ($c, $code) => $code === $activeCode);
    $othersFrom = $others->map(fn ($c, $code) => ($c['symbol'] ?? '').number_format(Currency::pricePerUser($code) * $minSeats))->implode(' · ');
    $bothFrom   = $symbol.number_format($fromMonth).($others->isNotEmpty() ? ' ('.$othersFrom.')' : '');

    // Sibling solutions for the cross-link section (everything except this page).
    $siblings = collect(config('landing.pages', []))
        ->except($key)
        ->map(fn ($p, $k) => ['key' => $k, 'title' => $p['title'], 'keyword' => $p['keyword']])
        ->values();
@endphp

@section('title', $page['title'])
@section('meta_description', $page['meta_description'])
@section('meta_keywords', $page['meta_keywords'])

@push('ld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => $page['service_type'],
    'name' => $page['title'].' — '.config('seo.site_name', 'Best CPMS'),
    'url' => route('landing.'.$key),
    'description' => $page['meta_description'],
    'provider' => ['@type' => 'Organization', 'name' => config('seo.site_name', 'Best CPMS'), 'url' => url('/')],
    'areaServed' => config('seo.region', 'IN'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Construction Project Management Software', 'item' => route('cpm-software')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $page['breadcrumb'], 'item' => route('landing.'.$key)],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@if (! empty($page['faqs']))
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($page['faqs'])->map(fn ($f) => [
        '@type' => 'Question',
        'name' => strip_tags($fill($f['q'])),
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($fill($f['a']))],
    ])->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
@endpush

@section('content')

    {{-- ============================ Hero ============================ --}}
    <section class="relative overflow-hidden border-b border-slate-200 bg-slate-50 blueprint-grid">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <nav class="mb-6 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-slate-900">Home</a>
                <span class="mx-2 text-slate-300">/</span>
                <a href="{{ route('cpm-software') }}" class="transition hover:text-slate-900">Construction PM Software</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-700">{{ $page['breadcrumb'] }}</span>
            </nav>

            <span class="kicker font-display text-sm font-medium text-amber-700">{{ $page['kicker'] }}</span>
            <h1 class="mt-4 max-w-3xl font-display text-4xl font-bold leading-[1.08] tracking-tight text-slate-900 sm:text-5xl">
                {{ $page['h1'] }}
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">{{ $fill($page['lead']) }}</p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-amber-600">Start your free trial</a>
                <a href="{{ route('pricing') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-800 transition hover:border-slate-400">See pricing</a>
            </div>
            <p class="mt-4 text-sm text-slate-500">{{ $trialDays }}-day free trial · No card required · From {{ $bothFrom }}/month</p>
        </div>
    </section>

    {{-- ===================== Intro prose ===================== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="space-y-5 text-lg leading-relaxed text-slate-700">
                @foreach ($page['intro'] as $para)
                    <p>{{ $fill($para) }}</p>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== Feature grid ===================== --}}
    <section class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="max-w-2xl font-display text-3xl font-bold tracking-tight text-slate-900">{{ $page['features_heading'] }}</h2>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($page['features'] as $feature)
                    @php($href = (! empty($feature['route']) && \Illuminate\Support\Facades\Route::has($feature['route'])) ? route($feature['route']) : null)
                    @if ($href)
                        <a href="{{ $href }}" class="group rounded-xl border border-slate-200 bg-white p-6 transition hover:border-indigo-300 hover:shadow-sm">
                            <h3 class="font-display text-lg font-semibold text-slate-900">{!! $fill($feature['title']) !!}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $fill($feature['body']) }}</p>
                            <span class="mt-4 inline-block text-sm font-semibold text-indigo-600 transition group-hover:text-indigo-700">Learn more</span>
                        </a>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-white p-6">
                            <h3 class="font-display text-lg font-semibold text-slate-900">{!! $fill($feature['title']) !!}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $fill($feature['body']) }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== Emphasis band ===================== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="rounded-xl border-l-2 border-amber-500 bg-slate-50 p-8">
                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900">{{ $page['band']['heading'] }}</h2>
                <p class="mt-3 text-lg leading-relaxed text-slate-600">{{ $fill($page['band']['body']) }}</p>
            </div>
        </div>
    </section>

    {{-- ===================== FAQ ===================== --}}
    @if (! empty($page['faqs']))
        <section class="border-y border-slate-200 bg-slate-50">
            <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
                <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">Frequently asked questions</h2>
                <dl class="mt-10 divide-y divide-slate-200 border-t border-slate-200">
                    @foreach ($page['faqs'] as $faq)
                        <div class="py-6">
                            <dt class="font-display text-lg font-semibold text-slate-900">{{ $fill($faq['q']) }}</dt>
                            <dd class="mt-3 text-base leading-relaxed text-slate-600">{{ $fill($faq['a']) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    {{-- ===================== More solutions (internal links) ===================== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900">Explore more solutions</h2>
            <p class="mt-3 max-w-2xl text-slate-600">Best CPMS is one <a href="{{ route('cpm-software') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-700">construction project management system</a> with a solution for every team and workflow.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($siblings as $sib)
                    <a href="{{ route('landing.'.$sib['key']) }}" class="group flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-5 py-4 transition hover:border-indigo-300 hover:bg-slate-50">
                        <span class="font-display text-sm font-semibold text-slate-900">{{ $sib['title'] }}</span>
                        <span aria-hidden="true" class="text-indigo-400 transition group-hover:translate-x-0.5 group-hover:text-indigo-600">&rarr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== CTA band ===================== --}}
    <section class="bg-slate-900">
        <div class="mx-auto max-w-5xl px-5 py-16 text-center lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-white">Ready to run your projects on one system?</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-slate-300">Start your {{ $trialDays }}-day free trial today — no card required — and set up your first project this afternoon.</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-amber-600">Start free trial</a>
                <a href="{{ route('cpm-software') }}" class="rounded-md border border-slate-600 px-6 py-3 text-base font-semibold text-white transition hover:border-slate-400">See the full system</a>
            </div>
        </div>
    </section>

@endsection
