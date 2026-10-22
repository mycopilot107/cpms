@extends('layouts.public')

@section('title', 'Construction Cost & BOQ Software')
@section('meta_description', 'Construction cost control and BOQ software — keep your bill of quantities, committed cost, spend and client payments in one ledger, with every total recomputed on the server.')
@section('meta_keywords', 'construction cost control software, BOQ software, bill of quantities software, construction budgeting software, construction estimating software')

@push('ld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => 'Construction cost control and BOQ software',
    'name' => 'Cost & budget control — Best CPMS',
    'url' => route('services.cost'),
    'description' => 'Construction cost and BOQ software: build the bill of quantities, track committed cost and spend, and record client payments in one ledger.',
    'provider' => ['@type' => 'Organization', 'name' => config('seo.site_name', 'Best CPMS'), 'url' => url('/')],
    'areaServed' => config('seo.region', 'IN'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Construction Cost & BOQ Software', 'item' => route('services.cost')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Services · Cost & Budget Control',
        'title'  => 'Know where the money is',
        'lead'   => 'Bill of quantities, expenses, and client payments in one ledger — committed cost and cash position, always current.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="space-y-5 text-lg leading-relaxed text-slate-700">
                <p>Budgets don't fail all at once — they leak, line by line, until the overrun is already spent. Best CPMS keeps the estimate, the spend, and the money coming in against the same structure, so you see the trend while you can still act on it.</p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach ([
                    ['Bill of quantities', 'Build a BOQ per project and measure actual spend against every line item.'],
                    ['Expense logging', 'Record expenses by category and see exactly where the budget is going.'],
                    ['Client payments', 'Track invoiced and received payments against the contract value.'],
                    ['Live cost view', 'Committed versus actual — per project and per category — without a spreadsheet in sight.'],
                ] as $f)
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 rounded-xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="font-display font-semibold text-slate-900">Who it's for</h2>
                <p class="mt-2 text-slate-600">Commercial and finance leads who need a single, current picture of cost and cash across every live project.</p>
            </div>

            <div class="mt-10 flex flex-wrap gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Start free trial</a>
                <a href="{{ route('features') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-800 transition hover:border-slate-400">See all features</a>
            </div>
        </div>
    </section>

@endsection
