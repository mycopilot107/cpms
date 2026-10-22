@extends('layouts.public')

@section('title', 'Download the Best CPMS field app for Android')
@section('meta_description', 'Download the Best CPMS mobile app for Android. Field workers sign in to see their assigned tasks and report status, progress and hours straight from site.')
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

                    <div class="mt-8">
                        @if ($available)
                            <a href="{{ route('app.download') }}"
                               class="inline-flex items-center gap-3 rounded-md bg-[#3DDC84] px-6 py-3 font-semibold text-slate-900 shadow-sm transition hover:bg-[#32c976] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                @include('partials.android-icon', ['class' => 'h-6 w-6'])
                                Download the Android app
                            </a>
                            <p class="mt-3 text-sm text-slate-500">
                                Android APK · {{ $apkSize }}@if ($apkUpdated) · updated {{ $apkUpdated->format('j M Y') }}@endif
                            </p>
                        @else
                            <span class="inline-flex items-center gap-3 rounded-md border border-slate-300 bg-slate-100 px-6 py-3 font-semibold text-slate-400">
                                @include('partials.android-icon', ['class' => 'h-6 w-6'])
                                Android app coming soon
                            </span>
                            <p class="mt-3 text-sm text-slate-500">The download will appear here as soon as the build is published.</p>
                        @endif
                    </div>

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
                    ['See your tasks', 'Everything assigned to you, with status, priority and due dates — no digging.'],
                    ['Report from site', 'Update status, set percent complete, and log the hours you actually worked.'],
                    ['Stays in sync', 'Signs in with your normal work account; updates show up for the office instantly.'],
                ] as $f)
                    <div class="rounded-xl border border-slate-200 p-6">
                        <h3 class="font-display font-semibold text-slate-900">{{ $f[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Install steps --}}
            <div class="mt-12 rounded-xl border border-slate-200 bg-slate-50 p-6 sm:p-8">
                <h2 class="font-display font-semibold text-slate-900">Installing on Android</h2>
                <p class="mt-2 text-sm text-slate-600">
                    The app is distributed as a direct download (APK), so the first install takes one extra tap to allow it.
                </p>
                <ol class="mt-5 space-y-4">
                    @foreach ([
                        ['Download the APK', 'Tap “Download for Android” above and open the file when it finishes.'],
                        ['Allow the install', 'Android will ask whether to allow installs from your browser or Files app — tap allow, then confirm the install.'],
                        ['Sign in', 'Open Best CPMS Field and sign in with the same email and password you use on the web.'],
                    ] as $i => $step)
                        <li class="flex gap-4">
                            <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">{{ $i + 1 }}</span>
                            <div>
                                <p class="font-medium text-slate-900">{{ $step[0] }}</p>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $step[1] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <p class="mt-6 text-xs text-slate-500">
                    Requires Android 6.0 or newer. Your device needs to reach the Best CPMS server — if your company runs Best CPMS on an internal network, make sure your phone is on the same network or VPN.
                </p>
            </div>

        </div>
    </section>

@endsection
