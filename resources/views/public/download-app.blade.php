@extends('layouts.public')

@section('title', 'Get the Best CPMS field app for Android')
@section('meta_description', 'Get the Best CPMS field app for Android on Google Play or as a direct APK download. Site staff see their tasks, file the daily site report, and send photos from site.')
@section('meta_keywords', 'construction field app, construction site app android, best cpms mobile app download, construction task app, site progress app')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Mobile app',
        'title'  => 'The site in your pocket',
        'lead'   => 'A companion app for field staff: sign in, see the tasks assigned to you, and report status, progress and hours — right from where the work is happening.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">

            @if (session('status'))
                <div class="mb-8 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid items-center gap-12 lg:grid-cols-2">

                {{-- Left: pitch + download --}}
                <div>
                    <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900">Built for the people on site</h2>
                    <p class="mt-4 text-lg leading-relaxed text-slate-600">
                        Reporting falls apart when the people closest to the work can't easily record it. The app is deliberately small and fast: open it, update a task, carry on. Everything syncs to the same account your office team uses on the web.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @if ($playUrl)
                            <a href="{{ $playUrl }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-3 rounded-lg bg-slate-900 px-5 py-3 text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#34A853" d="M3.6 1.8 13.8 12 3.6 22.2c-.4-.2-.6-.7-.6-1.2V3c0-.5.2-1 .6-1.2Z"/>
                                    <path fill="#FBBC04" d="m17.2 8.6-3.4 3.4 3.4 3.4 3.9-2.2c1-.6 1-2 0-2.5l-3.9-2.1Z"/>
                                    <path fill="#4285F4" d="M3.6 1.8c.3-.2.8-.2 1.2 0l12.4 6.8-3.4 3.4L3.6 1.8Z"/>
                                    <path fill="#EA4335" d="M3.6 22.2 13.8 12l3.4 3.4-12.4 6.8c-.4.2-.9.2-1.2 0Z"/>
                                </svg>
                                <span class="text-left leading-tight">
                                    <span class="block text-[11px] uppercase tracking-wide text-slate-300">{{ $playMode === 'testing' ? 'Join the test on' : 'Get it on' }}</span>
                                    <span class="block text-lg font-semibold">Google Play</span>
                                </span>
                            </a>
                        @endif

                        @if ($apkAvailable)
                            <a href="{{ route('app.download') }}" download
                               class="inline-flex items-center gap-3 rounded-lg bg-[#3DDC84] px-5 py-3 text-slate-900 shadow-sm transition hover:bg-[#32c574] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                @include('partials.android-icon', ['class' => 'h-7 w-7'])
                                <span class="text-left leading-tight">
                                    <span class="block text-[11px] uppercase tracking-wide text-slate-700">Direct download · {{ $apkSizeMb }} MB</span>
                                    <span class="block text-lg font-semibold">Download APK</span>
                                </span>
                            </a>
                        @endif

                        @if (! $playUrl && ! $apkAvailable)
                            <span class="inline-flex items-center gap-3 rounded-md border border-slate-300 bg-slate-100 px-6 py-3 font-semibold text-slate-400">
                                @include('partials.android-icon', ['class' => 'h-6 w-6'])
                                Coming soon to Google Play
                            </span>
                        @endif
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        @if ($playUrl && $apkAvailable)
                            Google Play installs and updates the app for you. No Play Store on the phone? Download the APK instead.
                        @elseif ($playUrl)
                            Installs and updates through the Play Store — no settings to change.
                        @elseif ($apkAvailable)
                            Download the APK straight to your phone. Google Play is coming soon.
                        @else
                            The Google Play link will appear here as soon as the app is published.
                        @endif
                    </p>

                    <p class="mt-6 text-sm text-slate-500">
                        On an iPhone or a desktop? The <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">web app</a> is fully responsive and works in any browser — the Android app is an extra convenience for field teams, not a requirement.
                    </p>
                </div>

                {{-- Right: a simple phone frame hinting at the app --}}
                <div class="flex justify-center lg:justify-end">
                    <div class="w-64 rounded-[2rem] border border-slate-200 bg-slate-900 p-3 shadow-xl">
                        <div class="overflow-hidden rounded-[1.5rem] bg-white">
                            <div class="bg-indigo-600 px-4 pb-5 pt-6 text-white">
                                <p class="text-xs font-medium text-indigo-200">Best CPMS Field</p>
                                <p class="mt-1 font-display text-lg font-semibold">My tasks</p>
                            </div>
                            <div class="space-y-3 p-4">
                                @foreach ([
                                    ['Pour column C-4', 'In progress', '60%', 'bg-indigo-500'],
                                    ['Formwork — L3 slab', 'To do', '0%', 'bg-slate-300'],
                                    ['Rebar inspection', 'Done', '100%', 'bg-emerald-500'],
                                ] as $t)
                                    <div class="rounded-lg border border-slate-200 p-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-slate-800">{{ $t[0] }}</p>
                                            <span class="text-[11px] font-medium text-slate-500">{{ $t[1] }}</span>
                                        </div>
                                        <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100">
                                            <div class="h-1.5 rounded-full {{ $t[3] }}" style="width: {{ $t[2] }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- What you can do --}}
            <div class="mt-16 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['Daily site report', 'What happened today, labour by trade, a note, and photos — filed in a minute.'],
                    ['Tasks and photos', 'See what is assigned to you, update progress, and add site photos to the task.'],
                    ['Stays in sync', 'Signs in with your normal work account; the office sees updates straight away.'],
                ] as $f)
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Install steps --}}
            @php
                $steps = $playMode === 'testing'
                    ? [
                        ['Send us your Google account', 'The app is in early access. Ask your company owner, or contact Best CPMS, to add the Gmail / Google account you use on your phone as a tester.'],
                        ['Accept the invite', 'On your phone, open the Google Play link above with that account and tap “Become a tester”, then “Download it on Google Play”.'],
                        ['Install and sign in', 'Install Best CPMS Field from the Play Store and sign in with the same email and password you use on the web.'],
                    ]
                    : [
                        ['Open Google Play', 'Tap “Get it on Google Play” above, or search the Play Store for Best CPMS Field.'],
                        ['Install', 'Tap Install. Updates arrive through the Play Store automatically.'],
                        ['Sign in', 'Open Best CPMS Field and sign in with the same email and password you use on the web.'],
                    ];
                $apkSteps = [
                    ['Download the APK', 'Tap “Download APK” above on your phone and wait for the download to finish.'],
                    ['Open the file and allow the install', 'Open BestCPMS-Field.apk from the notification or your Downloads. The first time, Android asks you to allow installs from your browser — tap Settings, turn on “Allow from this source”, then go back and tap Install.'],
                    ['Sign in', 'Open Best CPMS Field and sign in with the same email and password you use on the web. For a new version, download the APK again and install it over the old one.'],
                ];
                $installGuides = array_values(array_filter([
                    ($playUrl || ! $apkAvailable) ? [
                        'title' => 'Installing from Google Play',
                        'intro' => 'The app comes from Google Play, so there is nothing to allow in your phone’s settings.',
                        'steps' => $steps,
                    ] : null,
                    $apkAvailable ? [
                        'title' => 'Installing the APK',
                        'intro' => 'For phones without Google Play, or when you need the app right now.',
                        'steps' => $apkSteps,
                    ] : null,
                ]));
            @endphp
            <div class="mt-12 grid gap-6 {{ count($installGuides) > 1 ? 'lg:grid-cols-2' : '' }}">
                @foreach ($installGuides as $guide)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 sm:p-8">
                        <h2 class="font-display font-semibold text-slate-900">{{ $guide['title'] }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $guide['intro'] }}</p>
                        <ol class="mt-5 space-y-4">
                            @foreach ($guide['steps'] as $i => $step)
                                <li class="flex gap-4">
                                    <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">{{ $i + 1 }}</span>
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $step[0] }}</p>
                                        <p class="mt-0.5 text-sm text-slate-600">{{ $step[1] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </div>
            <p class="mt-6 text-xs text-slate-500">
                Requires Android 7.0 or newer and an internet connection (Wi-Fi or mobile data).
                @if ($playUrl && $apkAvailable)
                    The Play Store version and the APK can’t update each other — to switch, uninstall the app first.
                @endif
                @if ($playMode === 'testing')
                    Need to be added as a tester? <a href="{{ route('contact') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Contact us</a>.
                @endif
            </p>

        </div>
    </section>

@endsection
