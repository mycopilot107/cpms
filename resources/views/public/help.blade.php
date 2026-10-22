@extends('layouts.public')

@section('title', 'Help Center')
@section('meta_description', 'Guides and answers to help you get the most out of Best CPMS — from setup to reporting.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Help Center',
        'title'  => 'How can we help?',
        'lead'   => 'Browse guides by topic, or reach a human whenever you need one.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            @php
                $topics = [
                    ['title' => 'Getting started', 'desc' => 'Create your account, set up your first project, and invite your team.', 'articles' => ['Set up your company account', 'Create your first project', 'Invite team members & assign roles']],
                    ['title' => 'Planning & scheduling', 'desc' => 'Milestones, tasks, the calendar, and the Gantt view.', 'articles' => ['Add milestones & tasks', 'Read the Gantt timeline', 'Handle overdue work']],
                    ['title' => 'Cost & budgets', 'desc' => 'Build a BOQ, log expenses, and track client payments.', 'articles' => ['Create a bill of quantities', 'Record and categorise expenses', 'Track client payments']],
                    ['title' => 'Reports', 'desc' => 'Project, task, cost, and progress reports, plus CSV export.', 'articles' => ['Generate a progress report', 'Export a report to CSV', 'Filter reports by project']],
                    ['title' => 'Account & billing', 'desc' => 'Trials, subscriptions, seats, and invoices.', 'articles' => ['Start or upgrade a subscription', 'Add or remove seats', 'Manage billing details']],
                    ['title' => 'Security & access', 'desc' => 'Roles, permissions, and how your data is isolated.', 'articles' => ['Understand roles & permissions', 'How tenant isolation works', 'Reset a password']],
                ];
            @endphp

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($topics as $t)
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h2 class="font-display text-lg font-semibold text-slate-900">{{ $t['title'] }}</h2>
                        <p class="mt-1.5 text-sm text-slate-600">{{ $t['desc'] }}</p>
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($t['articles'] as $a)
                                <li><a href="{{ route('contact') }}" class="text-indigo-600 hover:text-indigo-700">{{ $a }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="mt-14 grid gap-6 rounded-xl border border-slate-200 bg-slate-50 p-8 sm:grid-cols-3 sm:items-center">
                <div class="sm:col-span-2">
                    <h2 class="font-display text-xl font-bold text-slate-900">Can't find what you need?</h2>
                    <p class="mt-2 text-slate-600">Our support team typically replies within one business day. Check the FAQ for quick answers, or send us a message.</p>
                </div>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('faq') }}" class="rounded-md border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-semibold text-slate-800 transition hover:border-slate-400">Read the FAQ</a>
                    <a href="{{ route('contact') }}" class="rounded-md bg-amber-500 px-5 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-amber-600">Contact support</a>
                </div>
            </div>
        </div>
    </section>

@endsection
