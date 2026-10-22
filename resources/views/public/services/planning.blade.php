@extends('layouts.public')

@section('title', 'Construction Scheduling Software')
@section('meta_description', 'Construction scheduling software to sequence the whole build — milestones, tasks, dependencies, a shared calendar and a Gantt timeline that shows slippage before it spreads.')
@section('meta_keywords', 'construction scheduling software, construction planning software, gantt chart software for construction, construction project scheduling, cpm scheduling software')

@push('ld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => 'Construction scheduling software',
    'name' => 'Planning & scheduling — Best CPMS',
    'url' => route('services.planning'),
    'description' => 'Construction scheduling software: milestones, tasks, dependencies, a shared calendar and a Gantt timeline for the whole build.',
    'provider' => ['@type' => 'Organization', 'name' => config('seo.site_name', 'Best CPMS'), 'url' => url('/')],
    'areaServed' => config('seo.region', 'IN'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Construction Scheduling Software', 'item' => route('services.planning')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Services · Planning & Scheduling',
        'title'  => 'Sequence the whole build',
        'lead'   => 'Turn scope into a programme your team can follow — and see slippage the moment it appears, not at the next site meeting.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="space-y-5 text-lg leading-relaxed text-slate-700">
                <p>A plan is only useful if it stays true. Best CPMS keeps your programme connected to the work itself — so when a task slips, everything downstream reflects it, and the people who need to know, know.</p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach ([
                    ['Milestones & dependencies', 'Structure each project into milestones with owners, dates, and priorities, and link the work that depends on it.'],
                    ['Calendar view', 'A shared week and month view across every active project, so nothing due quietly slips past.'],
                    ['Gantt timeline', 'See sequence, overlap, and duration at a glance — and read the shape of the programme in seconds.'],
                    ['Overdue flags', 'Late work surfaces automatically and stays visible until it\'s resolved.'],
                ] as $f)
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 rounded-xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="font-display font-semibold text-slate-900">Who it's for</h2>
                <p class="mt-2 text-slate-600">Project managers and planners who need one programme the whole team trusts — from the site engineer to the developer.</p>
            </div>

            <div class="mt-10 flex flex-wrap gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Start free trial</a>
                <a href="{{ route('features') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-800 transition hover:border-slate-400">See all features</a>
            </div>
        </div>
    </section>

@endsection
