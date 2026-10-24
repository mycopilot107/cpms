<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Best CPMS')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full text-slate-800">
@php
    $user = auth()->user();
    $company = $user?->company;
    $isSuperAdmin = $user?->isSuperAdmin() ?? false;
@endphp
<div class="min-h-full">
    @php
        if ($isSuperAdmin) {
            // Super admins use the platform console only. Do not expose tenant
            // navigation such as Dashboard, Projects, Billing, or Team.
            $navItems = [
                ['url' => route('platform.companies.index'), 'label' => 'Companies', 'active' => request()->routeIs('platform.*')],
            ];
        } else {
            $navItems = [
                ['url' => route('dashboard'), 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard')],
                ['url' => route('projects.index'), 'label' => 'Projects', 'active' => request()->routeIs('projects.*')],
                ['url' => route('tasks.index'), 'label' => 'Tasks', 'active' => request()->routeIs('tasks.*')],
            ];

            // Planning (calendar / Gantt / milestones) is available to every active member.
            if ($user?->isActive()) {
                $navItems[] = ['url' => route('planning.calendar'), 'label' => 'Planning', 'active' => request()->routeIs('planning.*') || request()->routeIs('milestones.*')];
            }

            // Finance + Reports are limited to finance/report roles.
            if ($user?->canViewFinance()) {
                $navItems[] = ['url' => route('finance.index'), 'label' => 'Finance', 'active' => request()->routeIs('finance.*') || request()->routeIs('boq-items.*') || request()->routeIs('expenses.*') || request()->routeIs('project-payments.*')];
            }
            if ($user?->canViewReports()) {
                $navItems[] = ['url' => route('reports.index'), 'label' => 'Reports', 'active' => request()->routeIs('reports.*')];
            }

            if ($user?->isAdmin()) {
                $navItems[] = ['url' => route('users.index'), 'label' => 'Team', 'active' => request()->routeIs('users.*')];
                $navItems[] = ['url' => route('subscription.plans'), 'label' => 'Billing', 'active' => request()->routeIs('subscription.*')];
            }

            $storyAdmin = config('stories.admin_email');
            if ($storyAdmin && $user && strcasecmp(trim((string) $user->email), trim((string) $storyAdmin)) === 0) {
                $navItems[] = ['url' => route('admin.stories.index'), 'label' => 'Stories', 'active' => request()->routeIs('admin.stories.*')];
            }

            if ($user?->isActive()) {
                $navItems[] = ['url' => route('stories.index'), 'label' => 'Share your story', 'active' => request()->routeIs('stories.*')];
            }
        }
    @endphp
    <nav class="bg-slate-900 text-slate-100">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="{{ $isSuperAdmin ? route('platform.companies.index') : route('dashboard') }}" class="flex items-center gap-2 text-lg font-bold text-white">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-white">C</span>
                        Best CPMS
                    </a>
                    <div class="hidden gap-1 md:flex">
                        @foreach ($navItems as $item)
                            <a href="{{ $item['url'] }}" class="rounded-md px-3 py-2 text-sm font-medium {{ $item['active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <div class="text-sm font-medium text-white">{{ auth()->user()?->name }}</div>
                        <div class="text-xs text-slate-400">{{ $company?->name }} · {{ auth()->user()?->role?->name }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                        @csrf
                        <button type="submit" class="rounded-md border border-slate-700 px-3 py-1.5 text-sm text-slate-200 hover:bg-slate-800">Log out</button>
                    </form>
                     <button type="button" id="mobile-menu-button" aria-controls="mobile-menu" aria-expanded="false"
                             @if ($isSuperAdmin) hidden @endif
                            class="inline-flex items-center justify-center rounded-md p-2 text-slate-300 hover:bg-slate-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-slate-500 md:hidden">
                        <span class="sr-only">Toggle navigation menu</span>
                        <svg id="mobile-menu-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg id="mobile-menu-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-slate-800 md:hidden">
            <div class="space-y-1 px-2 py-3">
                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" class="block rounded-md px-3 py-2 text-base font-medium {{ $item['active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">{{ $item['label'] }}</a>
                @endforeach
            </div>
            <div class="border-t border-slate-800 px-4 py-3">
                <div class="text-sm font-medium text-white">{{ auth()->user()?->name }}</div>
                <div class="text-xs text-slate-400">{{ $company?->name }} · {{ auth()->user()?->role?->name }}</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-md border border-slate-700 px-3 py-2 text-left text-sm text-slate-200 hover:bg-slate-800">Log out</button>
                </form>
            </div>
        </div>
    </nav>

    @php $activeSub = $company?->activeSubscription(); @endphp
    @if (! $isSuperAdmin && $company && $company->isOnTrial() && ! $activeSub)
        <div class="border-b border-amber-200 bg-amber-50 py-2 text-center text-sm text-amber-800">
            You're on a free trial — <span class="font-semibold">{{ $company->trialDaysRemaining() }}</span> day(s) remaining.
        </div>
    @elseif (! $isSuperAdmin && $company && $company->trialExpired() && ! $activeSub)
        <div class="border-b border-red-200 bg-red-50 py-2 text-center text-sm text-red-800">
            Your free trial has ended. <a href="{{ route('subscription.expired') }}" class="font-semibold underline">See options</a>.
        </div>
    @elseif (! $isSuperAdmin && $activeSub && $activeSub->ends_at && $activeSub->ends_at->between(now(), now()->addDays(30)))
        <div class="border-b border-amber-200 bg-amber-50 py-2 text-center text-sm text-amber-800">
            Your subscription expires on <span class="font-semibold">{{ $activeSub->ends_at->format('d M Y') }}</span>.
            @if (auth()->user()?->isAdmin())
                <a href="{{ route('subscription.plans') }}" class="font-semibold underline">Renew now</a>.
            @endif
        </div>
    @endif

    <header class="bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-4">
            <h1 class="text-xl font-semibold text-slate-900">@yield('heading', 'Dashboard')</h1>
            <div class="flex flex-wrap items-center gap-2">@yield('actions')</div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-medium">Please fix the following:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    (function () {
        var btn = document.getElementById('mobile-menu-button');
        var menu = document.getElementById('mobile-menu');
        var openIcon = document.getElementById('mobile-menu-open');
        var closeIcon = document.getElementById('mobile-menu-close');
        if (!btn || !menu) return;

        btn.addEventListener('click', function () {
            var isHidden = menu.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', String(!isHidden));
            if (openIcon && closeIcon) {
                openIcon.classList.toggle('hidden', !isHidden);
                closeIcon.classList.toggle('hidden', isHidden);
            }
        });
    })();
</script>
</body>
</html>
