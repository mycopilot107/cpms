@extends('layouts.public')

@section('title', 'Construction Management Software Features')
@section('meta_description', 'Planning and scheduling, cost control, progress tracking, reporting, and role-based access — everything in Best CPMS, module by module.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Features',
        'title'  => 'The whole build, covered end to end',
        'lead'   => 'Best CPMS is organised around how a project actually runs — plan it, cost it, track it, and report on it — with the right people seeing the right things.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-6xl space-y-20 px-5 py-16 sm:py-20 lg:px-8">

            {{-- Planning --}}
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-4">
                    <h2 class="font-display text-2xl font-bold text-slate-900">Planning &amp; scheduling</h2>
                    <p class="mt-3 text-slate-600">Turn a scope into a sequenced programme your whole team can follow.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:col-span-8">
                    @foreach ([
                        ['Milestones & tasks', 'Break each project into milestones with owners, dates, priorities, and dependencies.'],
                        ['Calendar view', 'See everything due this week or month across every active project.'],
                        ['Gantt timeline', 'Visualise sequence and overlap, and spot slippage before it spreads.'],
                        ['Overdue flags', 'Late work surfaces automatically so nothing quietly slips.'],
                    ] as $f)
                        <div class="border-l-2 border-slate-200 pl-4">
                            <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cost --}}
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-4">
                    <h2 class="font-display text-2xl font-bold text-slate-900">Cost &amp; budget control</h2>
                    <p class="mt-3 text-slate-600">Keep committed cost, spend, and client payments in one ledger.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:col-span-8">
                    @foreach ([
                        ['Bill of quantities', 'Build a BOQ per project and measure spend against each line.'],
                        ['Expense logging', 'Record expenses by category and see where the budget is going.'],
                        ['Client payments', 'Track invoiced and received payments against the contract value.'],
                        ['Live cost view', 'Committed vs. actual, per project and per category, at a glance.'],
                    ] as $f)
                        <div class="border-l-2 border-slate-200 pl-4">
                            <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Reporting --}}
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-4">
                    <h2 class="font-display text-2xl font-bold text-slate-900">Reporting &amp; access</h2>
                    <p class="mt-3 text-slate-600">Answer &ldquo;where are we?&rdquo; instantly — and keep data to the people who should see it.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:col-span-8">
                    @foreach ([
                        ['Project & task reports', 'Filterable status across projects, exportable to CSV.'],
                        ['Cost & progress reports', 'Budget burn and completion, ready for the client meeting.'],
                        ['Role-based access', 'Owners, admins, and members see only what their role allows.'],
                        ['Multi-company isolation', 'Each company\'s data is fully separated — never shared or visible across tenants.'],
                    ] as $f)
                        <div class="border-l-2 border-slate-200 pl-4">
                            <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-4xl flex-col items-center gap-4 px-5 py-14 text-center lg:px-8">
            <h2 class="font-display text-2xl font-bold text-slate-900">See it on your own project</h2>
            <p class="max-w-xl text-slate-600">Start a free trial and load a live project in an afternoon.</p>
            <a href="{{ route('register') }}" class="mt-2 rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Start your free trial</a>
        </div>
    </section>

@endsection
