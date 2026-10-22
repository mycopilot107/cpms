@extends('layouts.public')

@section('title', 'About Best CPMS')
@section('meta_description', 'Best CPMS is built by people who have run construction projects and the software behind them. Here is our story.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'About Us',
        'title'  => 'We build software for people who build.',
        'lead'   => 'Best CPMS started on a job site, not in a boardroom — from the frustration of running a live project across a scheduling tool, three spreadsheets, and a group chat.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="space-y-6 text-lg leading-relaxed text-slate-700">
                <p>Our founders spent years between the site office and the server room. They watched capable teams lose days each month reconciling what the programme said with what the budget said with what had actually been built. The information existed — it was just scattered, stale, and impossible to trust as a whole.</p>
                <p>Best CPMS is our answer: one workspace where planning, cost, and progress stay in step, built specifically for how construction projects run in the real world. It is opinionated where it should be — money and data separation are handled carefully and deliberately — and flexible everywhere else.</p>
                <p>Today, teams use Best CPMS to run projects from tender through handover without losing the thread. We are a small, focused company, and we intend to stay close to the people doing the work.</p>
            </div>

            <dl class="mt-14 grid grid-cols-2 gap-8 border-t border-slate-200 pt-10 sm:grid-cols-4">
                @foreach ([
                    ['2,400+', 'Active projects'],
                    ['18', 'Countries'],
                    ['99.9%', 'Uptime target'],
                    ['4.8/5', 'Avg. review'],
                ] as $stat)
                    <div>
                        <dt class="font-display text-3xl font-bold text-slate-900">{{ $stat[0] }}</dt>
                        <dd class="mt-1 text-sm text-slate-500">{{ $stat[1] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto grid max-w-6xl gap-6 px-5 py-14 sm:grid-cols-3 lg:px-8">
            <a href="{{ route('team') }}" class="rounded-xl border border-slate-200 bg-white p-6 transition hover:border-slate-300">
                <h3 class="font-display font-semibold text-slate-900">Our team</h3>
                <p class="mt-2 text-sm text-slate-600">The people building Best CPMS.</p>
            </a>
            <a href="{{ route('careers') }}" class="rounded-xl border border-slate-200 bg-white p-6 transition hover:border-slate-300">
                <h3 class="font-display font-semibold text-slate-900">Careers</h3>
                <p class="mt-2 text-sm text-slate-600">Help us build it. We're hiring.</p>
            </a>
            <a href="{{ route('mission') }}" class="rounded-xl border border-slate-200 bg-white p-6 transition hover:border-slate-300">
                <h3 class="font-display font-semibold text-slate-900">Mission &amp; vision</h3>
                <p class="mt-2 text-sm text-slate-600">Why we do this.</p>
            </a>
        </div>
    </section>

@endsection
