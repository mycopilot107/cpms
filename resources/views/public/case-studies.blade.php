@extends('layouts.public')

@section('title', 'Construction Project Case Studies')
@section('meta_description', 'How construction teams use Best CPMS to ship projects on time and on budget.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Case Studies',
        'title'  => 'Real projects, run on Best CPMS',
        'lead'   => 'A look at how different teams put the platform to work — and the numbers that moved.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-5xl px-5 py-16 sm:py-20 lg:px-8">
            @php
                // Illustrative case studies for layout.
                $cases = [
                    [
                        'company' => 'Hearthstone Developers',
                        'sector'  => 'Residential · 3 towers',
                        'headline'=> 'Cut month-end reporting from a week to an afternoon',
                        'summary' => 'By moving the programme and the cost ledger into one workspace, the projects team stopped reconciling spreadsheets and started closing the month in hours.',
                        'stats'   => [['80%', 'Less time on reporting'], ['3', 'Projects unified'], ['0', 'Spreadsheets at month-end']],
                    ],
                    [
                        'company' => 'Delta Build Contractors',
                        'sector'  => 'Commercial fit-out',
                        'headline'=> 'Caught slippage three weeks earlier',
                        'summary' => 'Overdue flags and the Gantt view surfaced a sequencing clash before it hit the critical path, saving a costly re-mobilisation.',
                        'stats'   => [['3 wks', 'Earlier warning'], ['12', 'Active projects'], ['4.9/5', 'Team rating']],
                    ],
                    [
                        'company' => 'Northline PMC',
                        'sector'  => 'Project management consultancy',
                        'headline'=> 'Gave every client a clean, role-scoped view',
                        'summary' => 'Role-based access let Northline share live progress with clients while keeping commercial data private — no more manually redacted status decks.',
                        'stats'   => [['100%', 'Client-safe access'], ['9', 'Clients onboarded'], ['1 day', 'Avg. setup']],
                    ],
                ];
            @endphp

            <div class="space-y-8">
                @foreach ($cases as $c)
                    <article class="rounded-xl border border-slate-200 p-8">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="font-display text-xl font-bold text-slate-900">{{ $c['company'] }}</h2>
                            <span class="text-sm text-slate-500">{{ $c['sector'] }}</span>
                        </div>
                        <p class="mt-4 font-display text-lg font-semibold text-indigo-700">{{ $c['headline'] }}</p>
                        <p class="mt-2 leading-relaxed text-slate-600">{{ $c['summary'] }}</p>
                        <dl class="mt-6 grid grid-cols-3 gap-4 border-t border-slate-100 pt-6">
                            @foreach ($c['stats'] as $s)
                                <div>
                                    <dt class="font-display text-2xl font-bold text-slate-900">{{ $s[0] }}</dt>
                                    <dd class="mt-0.5 text-xs text-slate-500">{{ $s[1] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </article>
                @endforeach
            </div>

            <p class="mt-8 text-sm text-slate-400">Case studies are illustrative and shown for layout purposes.</p>

            <div class="mt-12 flex flex-col items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-8 text-center">
                <h2 class="font-display text-2xl font-bold text-slate-900">Could this be your project?</h2>
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Start your free trial</a>
            </div>
        </div>
    </section>

@endsection
