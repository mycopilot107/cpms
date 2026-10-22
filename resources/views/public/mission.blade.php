@extends('layouts.public')

@section('title', 'Mission & Vision')
@section('meta_description', 'Why Best CPMS exists: to close the gap between the plan and the site, and give construction teams one honest view of every project.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Mission & Vision',
        'title'  => 'Close the gap between the plan and the site.',
        'lead'   => 'Construction runs on plans that drift the moment work starts. We build the tools that keep the plan honest.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">

            <div class="border-l-2 border-amber-500 pl-6">
                <h2 class="font-display text-2xl font-bold text-slate-900">Our mission</h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-700">
                    To give every construction team a single, trustworthy view of their projects — where the schedule, the budget, and the work on site tell the same story. When information stops living in scattered spreadsheets and group chats, better decisions get made earlier, and fewer surprises reach the client.
                </p>
            </div>

            <div class="mt-14 border-l-2 border-indigo-500 pl-6">
                <h2 class="font-display text-2xl font-bold text-slate-900">Our vision</h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-700">
                    A future where a project's real status is never more than a glance away — for the site engineer, the finance lead, and the developer alike. We want Best CPMS to be the quiet backbone that lets teams spend less time reconciling reports and more time building.
                </p>
            </div>

            <div class="mt-16">
                <h2 class="font-display text-2xl font-bold text-slate-900">What we believe</h2>
                <div class="mt-8 grid gap-8 sm:grid-cols-3">
                    @foreach ([
                        ['Truth over optimism', 'A tool should show what is actually happening, not what everyone hopes is happening.'],
                        ['Built for the field', 'If it does not work with dusty hands on a phone at site, it does not work.'],
                        ['Boring where it counts', 'Money and data isolation are not features to move fast on. We are deliberate here.'],
                    ] as $v)
                        <div>
                            <h3 class="font-display font-semibold text-slate-900">{{ $v[0] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $v[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-4xl flex-col items-center gap-4 px-5 py-14 text-center lg:px-8">
            <h2 class="font-display text-2xl font-bold text-slate-900">Meet the people behind Best CPMS</h2>
            <a href="{{ route('team') }}" class="mt-2 rounded-md border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-800 transition hover:border-slate-400">See our team</a>
        </div>
    </section>

@endsection
