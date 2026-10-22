@extends('layouts.public')

@section('title', 'Construction Site Progress Tracking')
@section('meta_description', 'Construction site progress tracking software — update progress from site, flag risks, record actual hours, and pull reports the moment anyone asks. Built to work on a phone.')
@section('meta_keywords', 'construction site progress tracking, construction field reporting software, daily progress report construction, construction site management software, construction field app')

@push('ld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => 'Construction site progress tracking',
    'name' => 'Field & progress tracking — Best CPMS',
    'url' => route('services.field'),
    'description' => 'Construction site progress tracking: update progress from site, flag risks, record actual hours, and pull reports on demand — on any phone.',
    'provider' => ['@type' => 'Organization', 'name' => config('seo.site_name', 'Best CPMS'), 'url' => url('/')],
    'areaServed' => config('seo.region', 'IN'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Construction Site Progress Tracking', 'item' => route('services.field')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Services · Field & Progress Tracking',
        'title'  => 'What\'s actually built, in real time',
        'lead'   => 'Update progress from site, flag risks early, and turn status into a report the moment someone asks for one.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="space-y-5 text-lg leading-relaxed text-slate-700">
                <p>Reporting breaks down when the people closest to the work can't easily record it. Best CPMS is built to be updated from site — on a phone, with dusty hands — so progress data is fresh, and the report you hand the client reflects reality.</p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach ([
                    ['Progress updates from site', 'Mark work complete and log status from wherever the work is happening.'],
                    ['Risk flags', 'Raise and track risks against tasks so problems don\'t stay quiet.'],
                    ['Project & task reports', 'Filterable status across every project, exportable to CSV in a click.'],
                    ['Cost & progress reports', 'Completion and budget burn, ready for the next client meeting.'],
                ] as $f)
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 rounded-xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="font-display font-semibold text-slate-900">Who it's for</h2>
                <p class="mt-2 text-slate-600">Site engineers and project managers who need progress captured where it happens and reporting that writes itself.</p>
            </div>

            <div class="mt-10 flex flex-wrap gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Start free trial</a>
                <a href="{{ route('features') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-800 transition hover:border-slate-400">See all features</a>
            </div>
        </div>
    </section>

@endsection
