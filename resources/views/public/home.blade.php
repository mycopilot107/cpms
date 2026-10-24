@extends('layouts.public')

@section('title', 'Construction project management, in one place')
@section('meta_description', 'Best CPMS helps construction teams plan schedules, control budgets, and track site progress — from tender to handover — in a single workspace.')

@push('ld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => 'Best CPMS — Construction Project Management Software',
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem' => 'Web, iOS, Android',
    'url' => url('/'),
    'description' => config('seo.default_description'),
    'offers' => collect(config('billing.currencies', []))->map(fn ($c) => [
        '@type' => 'Offer',
        'price' => (string) ((int) ($c['price_per_user'] ?? 0)),
        'priceCurrency' => $c['code'] ?? '',
        'description' => 'Per user, per month',
    ])->values()->all(),
    'publisher' => ['@type' => 'Organization', 'name' => config('seo.site_name', 'Best CPMS'), 'url' => url('/')],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    {{-- ============================ Hero ============================ --}}
    <section class="relative overflow-hidden border-b border-slate-200 bg-slate-50 blueprint-grid">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-5 py-16 sm:py-20 lg:grid-cols-12 lg:gap-8 lg:px-8">
            <div class="lg:col-span-6">
                <span class="kicker font-display text-sm font-medium text-amber-700">Built for construction teams</span>
                <h1 class="mt-4 font-display text-4xl font-bold leading-[1.08] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    Every project, from tender to handover, on one schedule.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                    Best CPMS keeps your programme, budget, and site progress in the same place — so the plan on paper matches what's actually happening on site.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-amber-600">Start your free trial</a>
                    <a href="{{ route('features') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-800 transition hover:border-slate-400">See what's inside</a>
                </div>
                <p class="mt-4 text-sm text-slate-500">15-day trial · No card required · Set up in an afternoon</p>
            </div>

            {{-- Product-grounded visual: a live-looking programme strip --}}
            <div class="lg:col-span-6">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <p class="font-display text-sm font-semibold text-slate-900">Riverside Tower — Programme</p>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">On track</span>
                    </div>

                    {{-- Month ruler --}}
                    <div class="mt-4 grid grid-cols-6 text-center text-[11px] font-medium text-slate-400">
                        <span>Apr</span><span>May</span><span>Jun</span><span>Jul</span><span>Aug</span><span>Sep</span>
                    </div>

                    {{-- Task rows: bar offset/width expressed as % of the timeline --}}
                    @php
                        $rows = [
                            ['label' => 'Site setup',    'start' => 0,  'width' => 16, 'tone' => 'bg-slate-400'],
                            ['label' => 'Foundations',   'start' => 12, 'width' => 22, 'tone' => 'bg-indigo-500'],
                            ['label' => 'Superstructure','start' => 30, 'width' => 30, 'tone' => 'bg-indigo-600'],
                            ['label' => 'MEP rough-in',  'start' => 52, 'width' => 24, 'tone' => 'bg-indigo-500'],
                            ['label' => 'Finishes',      'start' => 70, 'width' => 22, 'tone' => 'bg-slate-400'],
                            ['label' => 'Handover',      'start' => 90, 'width' => 10, 'tone' => 'bg-emerald-500'],
                        ];
                    @endphp
                    <div class="relative mt-3 space-y-2.5">
                        {{-- "Today" marker --}}
                        <div class="pointer-events-none absolute inset-y-0 z-10 border-l-2 border-dashed border-amber-500" style="left: 48%;">
                            <span class="absolute -top-0.5 left-1 rounded bg-amber-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">Today</span>
                        </div>
                        @foreach ($rows as $r)
                            <div class="flex items-center gap-3">
                                <span class="w-24 shrink-0 text-right text-xs text-slate-500">{{ $r['label'] }}</span>
                                <div class="relative h-5 flex-1 rounded bg-slate-100">
                                    <div class="absolute inset-y-0 rounded {{ $r['tone'] }}" style="left: {{ $r['start'] }}%; width: {{ $r['width'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4 text-center">
                        <div><p class="font-display text-xl font-bold text-slate-900">62%</p><p class="text-xs text-slate-500">Complete</p></div>
                        <div><p class="font-display text-xl font-bold text-slate-900">{{ ($currency['code'] ?? 'INR') === 'INR' ? '₹4.2Cr' : ($currency['symbol'] ?? '$').'510K' }}</p><p class="text-xs text-slate-500">Committed</p></div>
                        <div><p class="font-display text-xl font-bold text-slate-900">3</p><p class="text-xs text-slate-500">Open risks</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================ Trusted-by strip ============================ --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-5 py-8 lg:px-8">
            <p class="text-center text-sm text-slate-500">Trusted by contractors, developers, and PMCs managing <span class="font-semibold text-slate-700">5+ active projects</span></p>
        </div>
    </section>

    {{-- ============================ Core capabilities ============================ --}}
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="max-w-2xl">
                <span class="kicker font-display text-sm font-medium text-amber-700">What Best CPMS does</span>
                <h2 class="mt-4 font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Three jobs, one source of truth</h2>
                <p class="mt-4 text-lg text-slate-600">Stop reconciling a scheduling tool, three spreadsheets, and a WhatsApp group. Best CPMS keeps planning, cost, and progress in step.</p>
            </div>

            <div class="mt-12 grid gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-200 sm:grid-cols-3">
                <div class="bg-white p-7">
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    </div>
                    <h3 class="mt-5 font-display text-lg font-semibold text-slate-900">Plan the programme</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Break work into milestones and tasks, sequence them on a calendar and Gantt, and see slippage before it costs you.</p>
                    <a href="{{ route('services.planning') }}" class="mt-4 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-700">Planning &amp; scheduling</a>
                </div>
                <div class="bg-white p-7">
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <h3 class="mt-5 font-display text-lg font-semibold text-slate-900">Control the budget</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Build a BOQ, log expenses against it, and track client payments — so committed cost and cash position are never a surprise.</p>
                    <a href="{{ route('services.cost') }}" class="mt-4 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-700">Cost &amp; budget control</a>
                </div>
                <div class="bg-white p-7">
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z"/></svg>
                    </div>
                    <h3 class="mt-5 font-display text-lg font-semibold text-slate-900">Track what's built</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Update progress from site, flag risks, and pull project, cost, and progress reports the moment someone asks for them.</p>
                    <a href="{{ route('services.field') }}" class="mt-4 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-700">Field &amp; progress tracking</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================ Single strong testimonial ============================ --}}
    @php
        // Live spotlight: the featured approved story, or the most recent
        // approved one. Falls back to sample content until a story is approved.
        $spotlight = $story ?? null;
        $spotlight = $spotlight ?: [
            'name'     => 'Meera Kulkarni',
            'org'      => 'Projects Director, Hearthstone Developers (illustrative)',
            'text'     => 'We closed the gap between the site programme and the finance report. Month-end used to take a week of chasing — now it\'s an afternoon.',
            'stars'    => 5,
            'initials' => 'MK',
        ];
    @endphp
    <section class="border-y border-slate-200 bg-slate-900">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <blockquote class="font-display text-2xl font-medium leading-snug text-white sm:text-3xl">
                &ldquo;{{ $spotlight['text'] }}&rdquo;
            </blockquote>
            <div class="mt-6 flex items-center gap-4">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-amber-500 font-display font-semibold text-white">{{ $spotlight['initials'] }}</span>
                <div>
                    <p class="font-semibold text-white">{{ $spotlight['name'] }}</p>
                    <p class="text-sm text-slate-400">{{ $spotlight['org'] }}</p>
                </div>
            </div>
            <a href="{{ route('reviews') }}" class="mt-8 inline-block text-sm font-semibold text-amber-400 hover:text-amber-300">Read more customer reviews</a>
        </div>
    </section>

    {{-- Priority keyword pages, linked from the homepage so crawlers find them. --}}
    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">Construction software, by the job you need done</h2>
            <p class="mt-4 max-w-2xl text-lg text-slate-600">Pages for the searches construction teams actually make, each tied to a part of the product.</p>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['landing.management', 'Construction management software', 'Run several sites from one company account, with roles for office and field.'],
                    ['landing.scheduling', 'Construction scheduling software', 'Milestones, dated tasks, a month calendar, and a Gantt from those dates.'],
                    ['landing.india', 'Construction project management software India', 'Plan, cost, and report jobs with INR pricing and Razorpay checkout.'],
                    ['landing.project-tracking', 'Construction project tracking software', 'Follow schedule, cost, and site progress on one project picture.'],
                    ['landing.budget', 'Construction budget management software', 'Hold the project budget against the BOQ, expenses, and payments.'],
                    ['landing.expenses', 'Construction expense tracking software', 'Record site spend on the project that actually consumed it.'],
                    ['landing.reporting', 'Construction project reporting software', 'Export project, task, cost, and progress reports to CSV.'],
                    ['landing.boq', 'Construction BOQ software', 'Price each line as quantity times rate, stored on the project.'],
                    ['landing.gantt', 'Construction Gantt chart software', 'Plot tasks that have start and due dates on a month timeline.'],
                    ['landing.milestones', 'Construction milestone tracking software', 'Mark each stage pending, in progress, completed, or delayed.'],
                    ['landing.payments', 'Construction payment tracking software', 'Record client receipts and site payouts on the job.'],
                ] as [$routeName, $label, $blurb])
                    <a href="{{ route($routeName) }}" class="group rounded-xl border border-slate-200 bg-slate-50 p-5 transition hover:border-indigo-300 hover:bg-white hover:shadow-sm">
                        <h3 class="font-display text-base font-semibold text-slate-900">{{ $label }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $blurb }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ Pricing teaser ============================ --}}
    @php
        use App\Support\Currency;

        // Active display currency, with the other currency shown alongside so
        // both INR and USD are visible (Best CPMS sells worldwide).
        $active     = $currency ?? Currency::active();
        $all        = $currencies ?? Currency::all();
        $activeCode = $active['code'] ?? Currency::default();
        $symbol     = $active['symbol'] ?? '₹';
        $perUser    = Currency::pricePerUser($active);
        $minSeats   = (int) config('billing.min_seats', 10);

        $others        = collect($all)->reject(fn ($c, $code) => $code === $activeCode);
        $othersPerUser = $others->map(fn ($c, $code) => ($c['symbol'] ?? '').number_format(Currency::pricePerUser($code)))->implode(' · ');
        $othersExample = $others->map(fn ($c, $code) => ($c['symbol'] ?? '').number_format(15 * 12 * Currency::pricePerUser($code)))->implode(' · ');
    @endphp
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <span class="kicker font-display text-sm font-medium text-amber-700">Straightforward pricing</span>
                    <h2 class="mt-4 font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ $symbol }}{{ number_format($perUser) }} per user, per month.</h2>
                    @if ($others->isNotEmpty())
                        <p class="mt-2 text-sm font-medium text-slate-500">or {{ $othersPerUser }} per user, per month</p>
                    @endif
                    <p class="mt-4 text-lg text-slate-600">No modules to unlock, no per-project fees. Pick your seats and term — the whole platform is included from {{ $minSeats }} users up.</p>
                    <a href="{{ route('pricing') }}" class="mt-6 inline-block rounded-md bg-slate-900 px-6 py-3 text-base font-semibold text-white transition hover:bg-slate-700">See pricing details</a>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-8">
                    <p class="font-display text-sm font-semibold text-slate-900">Example: 15 users, 12 months</p>
                    <p class="mt-3 font-display text-4xl font-bold text-slate-900">{{ $symbol }}{{ number_format(15 * 12 * $perUser) }}</p>
                    <p class="mt-1 text-sm text-slate-500">15 seats × 12 months × {{ $symbol }}{{ number_format($perUser) }}</p>
                    @if ($others->isNotEmpty())
                        <p class="mt-1 text-xs text-slate-400">or {{ $othersExample }}</p>
                    @endif
                    <ul class="mt-6 space-y-2 text-sm text-slate-600">
                        <li class="flex gap-2"><span class="text-emerald-600">✓</span> Planning, finance &amp; reporting included</li>
                        <li class="flex gap-2"><span class="text-emerald-600">✓</span> Unlimited projects</li>
                        <li class="flex gap-2"><span class="text-emerald-600">✓</span> Mobile access for site teams</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================ Final CTA ============================ --}}
    <section class="border-t border-slate-200 bg-slate-50 blueprint-grid">
        <div class="mx-auto max-w-4xl px-5 py-16 text-center sm:py-20 lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Put your next project on Best CPMS</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600">Start a free trial today, or talk to us about migrating your live projects.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-amber-600">Start your free trial</a>
                <a href="{{ route('contact') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-800 transition hover:border-slate-400">Talk to sales</a>
            </div>
        </div>
    </section>

@endsection
