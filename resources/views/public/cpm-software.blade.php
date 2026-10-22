@extends('layouts.public')

@section('title', 'Construction Project Management Software')
@section('meta_description', 'Construction project management software for builders, contractors and developers in India and worldwide. Plan schedules, control BOQ and costs, assign site tasks, and track progress from tender to handover — in one system. Pricing in INR and USD. Free trial, no card.')
@section('meta_keywords', 'construction project management software, construction project management system, construction management software India, construction scheduling software, BOQ software, construction cost control software, site progress tracking, project management software for construction companies, builder and contractor software')

@php
    use App\Support\Currency;

    // Active DISPLAY currency (shared sitewide by ShareDisplayCurrency). Best CPMS
    // sells worldwide, so prices show in the visitor's currency with the other
    // currency alongside — both INR and USD are always visible.
    $active     = $currency ?? Currency::active();
    $all        = $currencies ?? Currency::all();
    $activeCode = $active['code'] ?? Currency::default();
    $symbol     = $active['symbol'] ?? '₹';
    $perUser    = Currency::pricePerUser($active);
    $minSeats   = (int) config('billing.min_seats', 10);
    $fromMonth  = number_format($perUser * $minSeats);
    $trialDays  = \App\Models\Company::TRIAL_DAYS;

    // Dual-currency phrases: active first, the rest in parentheses
    // (e.g. "$12 (₹1,000)" and "$24 (₹2,000)").
    $others        = collect($all)->reject(fn ($c, $code) => $code === $activeCode);
    $othersPerUser = $others->map(fn ($c, $code) => ($c['symbol'] ?? '').number_format(Currency::pricePerUser($code)))->implode(' · ');
    $othersFrom    = $others->map(fn ($c, $code) => ($c['symbol'] ?? '').number_format(Currency::pricePerUser($code) * $minSeats))->implode(' · ');
    $bothPerUser   = $symbol.number_format($perUser).($others->isNotEmpty() ? ' ('.$othersPerUser.')' : '');
    $bothFrom      = $symbol.$fromMonth.($others->isNotEmpty() ? ' ('.$othersFrom.')' : '');

    // The modules that make up the system — each links to the deeper page.
    $modules = [
        ['Planning &amp; scheduling', 'Break the build into milestones and tasks, set dependencies, and read the whole programme on a calendar and Gantt timeline.', route('services.planning')],
        ['Cost, budget &amp; BOQ', 'Build your bill of quantities, track expenses against budget, and record payments in and out — with every total recomputed server-side.', route('services.cost')],
        ['Field &amp; progress tracking', 'Capture site progress, task status and actual hours from the field, so the plan on paper matches what is happening on the ground.', route('services.field')],
        ['Team &amp; roles', 'Invite your team with role-based access — owner, admin, manager, supervisor, accountant and site worker — each seeing only what they should.', route('features')],
        ['Reports &amp; exports', 'Project, task, cost and progress reports across every job, each exportable to CSV for billing, audits and client updates.', route('landing.reporting')],
        ['Subscription &amp; billing', 'Start on a free trial, then license the seats you need. Simple, transparent, per-user monthly pricing in INR and USD.', route('pricing')],
    ];

    // Who it is for.
    $audiences = [
        ['Builders &amp; contractors', 'Run multiple sites, crews and subcontractors from one programme instead of scattered spreadsheets and WhatsApp groups.'],
        ['Real-estate developers', 'See cost, schedule and progress across a whole portfolio of projects, from groundbreaking to handover.'],
        ['Project &amp; construction managers', 'Keep the plan, the budget and the site in sync, and spot slippage before it spreads downstream.'],
        ['Subcontractors &amp; specialists', 'Track your scope, tasks and hours against the main programme and report progress without the paperwork.'],
    ];

    // FAQ — rendered on-page AND emitted as FAQPage structured data (kept in sync).
    $faqs = [
        [
            'q' => 'What is a construction project management system?',
            'a' => 'A construction project management system (Best CPMS) is software that brings a construction project’s schedule, budget, people and site progress into one place. Instead of separate spreadsheets for the programme, the bill of quantities, expenses and daily reports, a Best CPMS links them — so when a task slips or a cost changes, everyone sees the up-to-date picture.',
        ],
        [
            'q' => 'Is Best CPMS suitable for small builders and contractors?',
            'a' => 'Yes. Best CPMS is built for construction teams of every size, from a single contractor running a few sites to a developer managing a large portfolio. You license only the seats you need, starting at '.$minSeats.' users, and you can add more as you grow.',
        ],
        [
            'q' => 'Can I manage BOQ and project costs?',
            'a' => 'Yes. You can build a bill of quantities with quantities and rates, track expenses by category, and record payments in and out. Every amount is recomputed on the server from your quantities and rates, so the figures you report are always consistent — the browser is never trusted for the total.',
        ],
        [
            'q' => 'Does it work on mobile for site teams?',
            'a' => 'The web app is fully responsive, so your office and site teams can use it from a phone or tablet. There is also a companion mobile app for field workers to view their assigned tasks and report status, progress and actual hours from site.',
        ],
        [
            'q' => 'How much does construction project management software cost?',
            'a' => 'Best CPMS uses simple per-user monthly pricing: '.$bothPerUser.' per user, per month. With a minimum of '.$minSeats.' users, plans start from '.$bothFrom.' per month. Prices are shown in both Indian Rupees (INR) and US Dollars (USD) — you can choose 1, 3, 6 or 12 months at checkout, and the total is always months × users × the per-user price.',
        ],
        [
            'q' => 'Is there a free trial?',
            'a' => 'Yes. Every new company starts with a '.$trialDays.'-day free trial — no credit card required. You get full access during the trial and only pay when you decide to continue.',
        ],
    ];
@endphp

@push('ld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => 'Best CPMS — Construction Project Management Software',
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem' => 'Web, iOS, Android',
    'url' => route('cpm-software'),
    'description' => 'Construction project management software for builders, contractors and developers — planning and scheduling, BOQ and cost control, field progress tracking, team roles and reports, in one system.',
    'offers' => collect($all)->map(fn ($c) => [
        '@type' => 'Offer',
        'price' => (string) ((int) ($c['price_per_user'] ?? 0)),
        'priceCurrency' => $c['code'] ?? '',
        'description' => 'Per user, per month',
    ])->values()->all(),
    'publisher' => [
        '@type' => 'Organization',
        'name' => config('seo.site_name', 'Best CPMS'),
        'url' => url('/'),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Construction Project Management Software', 'item' => route('cpm-software')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faqs)->map(fn ($f) => [
        '@type' => 'Question',
        'name' => strip_tags($f['q']),
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f['a'])],
    ])->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    {{-- ============================ Hero ============================ --}}
    <section class="relative overflow-hidden border-b border-slate-200 bg-slate-50 blueprint-grid">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <nav class="mb-6 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-slate-900">Home</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-700">Construction Project Management Software</span>
            </nav>

            <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-7">
                    <span class="kicker font-display text-sm font-medium text-amber-700">Built for construction teams in India &amp; beyond</span>
                    <h1 class="mt-4 font-display text-4xl font-bold leading-[1.08] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                        Construction project management software
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                        Best CPMS is one system for your whole project — plan the programme, control the budget and BOQ, assign site tasks, and track progress from tender to handover. No more scattered spreadsheets.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-amber-600">Start your free trial</a>
                        <a href="{{ route('pricing') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-800 transition hover:border-slate-400">See pricing</a>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">{{ $trialDays }}-day free trial · No card required · From {{ $bothFrom }}/month</p>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="font-display text-sm font-semibold text-slate-900">One system, the whole project lifecycle</p>
                        <ul class="mt-4 space-y-3 text-sm text-slate-600">
                            @foreach (['Tender &amp; estimation (BOQ)', 'Programme &amp; scheduling', 'Site execution &amp; tasks', 'Cost &amp; payment tracking', 'Progress reporting &amp; handover'] as $i => $step)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-md bg-indigo-50 font-display text-xs font-semibold text-indigo-700">{{ $i + 1 }}</span>
                                    <span>{!! $step !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== What is a Best CPMS ===================== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">What is a construction project management system?</h2>
            <div class="mt-6 space-y-5 text-lg leading-relaxed text-slate-700">
                <p>A construction project management system brings a project’s schedule, budget, people and site progress into a single workspace. Rather than keeping the programme in one spreadsheet, the bill of quantities in another, and daily progress in a chat group, a construction management platform links them — so a change on site is reflected in the plan, the cost and the reports at once.</p>
                <p>Best CPMS is that system, built specifically for how construction teams work: from estimation and the BOQ, through planning and scheduling, into day-to-day site execution, cost control and handover. It runs in the browser and on mobile, so the office and the site are always looking at the same, current picture.</p>
            </div>
        </div>
    </section>

    {{-- ===================== Modules ===================== --}}
    <section class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="max-w-2xl font-display text-3xl font-bold tracking-tight text-slate-900">Everything you need to run the job, in one place</h2>
            <p class="mt-4 max-w-2xl text-lg text-slate-600">Each part of the system is connected, so your programme, costs and site progress never drift apart.</p>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($modules as [$title, $body, $link])
                    <a href="{{ $link }}" class="group rounded-xl border border-slate-200 bg-white p-6 transition hover:border-indigo-300 hover:shadow-sm">
                        <h3 class="font-display text-lg font-semibold text-slate-900">{!! $title !!}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{!! $body !!}</p>
                        <span class="mt-4 inline-block text-sm font-semibold text-indigo-600 transition group-hover:text-indigo-700">Learn more</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== Who it's for ===================== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="max-w-2xl font-display text-3xl font-bold tracking-tight text-slate-900">Built for every construction team</h2>
            <div class="mt-12 grid gap-8 sm:grid-cols-2">
                @foreach ($audiences as [$who, $why])
                    <div class="border-l-2 border-amber-500 pl-5">
                        <h3 class="font-display text-lg font-semibold text-slate-900">{!! $who !!}</h3>
                        <p class="mt-2 text-base leading-relaxed text-slate-600">{!! $why !!}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== Solutions (internal links to landing pages) ===================== --}}
    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="max-w-2xl font-display text-3xl font-bold tracking-tight text-slate-900">Solutions for every team and workflow</h2>
            <p class="mt-4 max-w-2xl text-lg text-slate-600">The same system, framed for how you work — by who you are and by what you need to get done.</p>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (config('landing.pages', []) as $solKey => $sol)
                    <a href="{{ route('landing.'.$solKey) }}" class="group flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 transition hover:border-indigo-300 hover:shadow-sm">
                        <span class="font-display text-sm font-semibold text-slate-900">{{ $sol['title'] }}</span>
                        <span aria-hidden="true" class="flex-none text-indigo-400 transition group-hover:translate-x-0.5 group-hover:text-indigo-600">&rarr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== Why Best CPMS ===================== --}}
    <section class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="max-w-2xl font-display text-3xl font-bold tracking-tight text-slate-900">Why teams choose Best CPMS</h2>
            <div class="mt-12 grid gap-10 sm:grid-cols-3">
                @foreach ([
                    ['One source of truth', 'Schedule, cost and site progress live together, so the plan always reflects reality — not last week’s version.'],
                    ['Costs you can trust', 'BOQ totals, budgets and payments are computed on the server from your own quantities and rates, never from the browser.'],
                    ['Fast to adopt', 'No installation or build step. Invite your team, set up your first project in an afternoon, and work from any device.'],
                ] as [$h, $p])
                    <div>
                        <h3 class="font-display text-lg font-semibold text-slate-900">{{ $h }}</h3>
                        <p class="mt-3 text-base leading-relaxed text-slate-600">{{ $p }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== Pricing teaser ===================== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8 text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">Simple, per-user pricing</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600">
                {{ $bothPerUser }} per user, per month. Minimum {{ $minSeats }} users, so plans start from <span class="font-semibold text-slate-900">{{ $bothFrom }}/month</span>. Choose 1, 3, 6 or 12 months at checkout.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-amber-600">Start free trial</a>
                <a href="{{ route('pricing') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-800 transition hover:border-slate-400">View full pricing</a>
            </div>
        </div>
    </section>

    {{-- ===================== FAQ ===================== --}}
    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">Frequently asked questions</h2>
            <dl class="mt-10 divide-y divide-slate-200 border-t border-slate-200">
                @foreach ($faqs as $faq)
                    <div class="py-6">
                        <dt class="font-display text-lg font-semibold text-slate-900">{!! $faq['q'] !!}</dt>
                        <dd class="mt-3 text-base leading-relaxed text-slate-600">{!! $faq['a'] !!}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- ===================== CTA band ===================== --}}
    <section class="bg-slate-900">
        <div class="mx-auto max-w-5xl px-5 py-16 text-center lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-white">Ready to run your projects on one system?</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-slate-300">Start your {{ $trialDays }}-day free trial today — no card required — and set up your first project this afternoon.</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-amber-600">Start free trial</a>
                <a href="{{ route('features') }}" class="rounded-md border border-slate-600 px-6 py-3 text-base font-semibold text-white transition hover:border-slate-400">Explore features</a>
            </div>
        </div>
    </section>

@endsection
