@extends('layouts.public')

@section('title', 'How Best CPMS Works — Roles, Web and App Sign-in')
@section('meta_description', 'How a construction team starts on Best CPMS: the owner registers, adds the team with a role, and plans the work. See what each role does, and who signs in on the website or the Android app.')

@php
    // Where each role signs in, and what they see there. Keyed by roles.slug.
    $access = [
        'owner' => [
            'web' => 'Everything: dashboard, projects, tasks, planning, finance, reports, team, and billing.',
            'app' => 'Yes, with email and password. Shows tasks assigned to them.',
        ],
        'manager' => [
            'web' => 'Projects, tasks, planning, finance, and reports.',
            'app' => 'Yes. Shows tasks assigned to them.',
        ],
        'engineer' => [
            'web' => 'Dashboard, projects, the task list, and the calendar and Gantt. Updates their own tasks.',
            'app' => 'Yes — the main tool. Updates status, progress, and hours on their tasks.',
        ],
        'supervisor' => [
            'web' => 'Projects, tasks, and planning. Creates milestones and assigns tasks.',
            'app' => 'Yes. Shows tasks assigned to them.',
        ],
        'accountant' => [
            'web' => 'Projects, tasks, finance, and reports.',
            'app' => 'Yes. Shows tasks assigned to them, if any.',
        ],
        'worker' => [
            'web' => 'Dashboard, projects, the task list, and the calendar and Gantt. Updates their own tasks.',
            'app' => 'Yes — the main tool. Updates status, progress, and hours on their tasks.',
        ],
    ];

    $steps = [
        ['Register the company', 'The person who signs up becomes the Owner. The company starts a '.\App\Models\Company::TRIAL_DAYS.'-day free trial with no card.'],
        ['Add the team', 'The owner opens Team, adds each person with an email and password, and picks their role. That person signs in with those details.'],
        ['Create projects and tasks', 'The owner or a project manager creates the project. A project manager or supervisor adds milestones and dated tasks, and assigns each task to someone.'],
        ['Report from site', 'Engineers and site workers open the Android app or the website, see the tasks assigned to them, and update status, percent complete, and hours.'],
        ['Review cost and progress', 'The accountant and project manager keep the bill of quantities, expenses, and payments, and export reports for the client.'],
    ];
@endphp

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'How it works',
        'title'  => 'From sign-up to site updates',
        'lead'   => 'Everyone on a company account has one role. The role decides what they can do, and every role can sign in on the website and on the Android app.',
    ])

    {{-- Getting started --}}
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">Getting started</h2>
            <ol class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($steps as $index => [$title, $body])
                    <li class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-display text-sm font-semibold text-white">{{ $index + 1 }}</span>
                        <h3 class="mt-4 font-display font-semibold text-slate-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $body }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Roles and sign-in --}}
    <section id="roles" class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">Roles, and where each one signs in</h2>
            <p class="mt-4 max-w-3xl text-lg text-slate-600">The website is for planning, cost, and the full project picture. The Android app is for updating your own tasks from site.</p>

            <div class="mt-10 overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-display font-semibold text-slate-900">Role</th>
                            <th scope="col" class="px-5 py-3 font-display font-semibold text-slate-900">Their work</th>
                            <th scope="col" class="px-5 py-3 font-display font-semibold text-slate-900">Website</th>
                            <th scope="col" class="px-5 py-3 font-display font-semibold text-slate-900">Android app</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($roles as $role)
                            <tr>
                                <th scope="row" class="whitespace-nowrap px-5 py-4 align-top font-display font-semibold text-slate-900">{{ $role->name }}</th>
                                <td class="min-w-[18rem] px-5 py-4 align-top leading-relaxed text-slate-600">{{ $role->description }}</td>
                                <td class="min-w-[14rem] px-5 py-4 align-top leading-relaxed text-slate-600">{{ $access[$role->slug]['web'] ?? 'Yes.' }}</td>
                                <td class="min-w-[14rem] px-5 py-4 align-top leading-relaxed text-slate-600">{{ $access[$role->slug]['app'] ?? 'Yes.' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Sign-in rules --}}
    <section class="bg-white">
        <div class="mx-auto grid max-w-6xl gap-6 px-5 py-16 sm:py-20 md:grid-cols-2 lg:px-8">
            <div class="rounded-xl border border-slate-200 p-6">
                <h2 class="font-display text-xl font-semibold text-slate-900">Signing in on the website</h2>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-slate-600">
                    <li>Any active team member signs in at <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">the login page</a> with the email and password the owner set.</li>
                    <li>Google or Facebook sign-in works for an existing account when the provider has verified the same email. A new email creates a new company with you as Owner.</li>
                    <li>A member marked inactive cannot sign in.</li>
                    <li>When the trial or plan has ended, members sign in but see the upgrade screen until the owner renews.</li>
                </ul>
            </div>
            <div class="rounded-xl border border-slate-200 p-6">
                <h2 class="font-display text-xl font-semibold text-slate-900">Signing in on the Android app</h2>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-slate-600">
                    <li><a href="{{ route('app.download-page') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Download the app</a> and sign in with the same email and password as the website.</li>
                    <li>The app uses email and password only. An account made with Google or Facebook needs a password set by the owner before it can use the app.</li>
                    <li>The app lists the tasks assigned to you. You update status, percent complete, and hours. Planning, finance, and the team stay on the website.</li>
                    <li>A member marked inactive cannot sign in.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-4xl flex-col items-center gap-4 px-5 py-14 text-center lg:px-8">
            <h2 class="font-display text-2xl font-bold text-slate-900">Set up your team</h2>
            <p class="max-w-xl text-slate-600">Register as the owner, add your people with a role, and send your site team the app.</p>
            <div class="mt-2 flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Start your free trial</a>
                <a href="{{ route('app.download-page') }}" class="rounded-md border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-800 transition hover:border-slate-400">Get the Android app</a>
            </div>
        </div>
    </section>

@endsection
