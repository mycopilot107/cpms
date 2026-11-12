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
            $unreadMessages = \App\Models\ContactMessage::query()->whereNull('read_at')->count();
            $navItems = [
                ['url' => route('platform.overview'), 'label' => 'Overview', 'active' => request()->routeIs('platform.overview')],
                ['url' => route('platform.companies.index'), 'label' => 'Companies', 'active' => request()->routeIs('platform.companies.*')],
                ['url' => route('platform.payments.index'), 'label' => 'Payments', 'active' => request()->routeIs('platform.payments.*')],
                ['url' => route('platform.users.index'), 'label' => 'Users', 'active' => request()->routeIs('platform.users.*')],
                ['url' => route('platform.messages.index'), 'label' => 'Messages'.($unreadMessages ? " ({$unreadMessages})" : ''), 'active' => request()->routeIs('platform.messages.*')],
                ['url' => route('platform.smtp-test'), 'label' => 'SMTP test', 'active' => request()->routeIs('platform.smtp-test*')],
                ['url' => route('admin.send-email.index'), 'label' => 'Send email', 'active' => false],
            ];
        } else {
            $navItems = [
                ['url' => route('dashboard'), 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard')],
            ];

            foreach (\App\Support\AppPages::LINKS as $key => $link) {
                if (! $user?->canAccess($key)) {
                    continue;
                }

                $navItems[] = [
                    'url' => route($link['route']),
                    'label' => \App\Support\AppPages::LABELS[$key],
                    'active' => request()->routeIs(...$link['active']),
                ];

                if ($key === 'team' && $user?->isAdmin()) {
                    $navItems[] = ['url' => route('roles.index'), 'label' => 'Roles', 'active' => request()->routeIs('roles.*')];
                }
            }

            $storyAdmin = config('stories.admin_email');
            if ($storyAdmin && $user && strcasecmp(trim((string) $user->email), trim((string) $storyAdmin)) === 0) {
                $navItems[] = ['url' => route('admin.stories.index'), 'label' => 'Stories', 'active' => request()->routeIs('admin.stories.*')];
            }
        }
    @endphp
    {{-- Keeps both translate utilities in the page so the CDN stylesheet never drops them. --}}
    <span class="hidden -translate-x-full translate-x-0" aria-hidden="true"></span>
    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-100 transition-transform md:translate-x-0">
        <a href="{{ $isSuperAdmin ? route('platform.overview') : route('dashboard') }}" class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-800 px-4 text-lg font-bold text-white">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-white">C</span>
            Best CPMS
        </a>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Main">
            @foreach ($navItems as $item)
                <a href="{{ $item['url'] }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ $item['active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-slate-800 px-3 py-4">
            <div class="px-3">
                <div class="truncate text-sm font-medium text-white">{{ auth()->user()?->name }}</div>
                <div class="truncate text-xs text-slate-400">{{ $company?->name }} · {{ auth()->user()?->role?->name }}</div>
            </div>
            <a href="{{ route('account.profile') }}" class="mt-3 block rounded-md px-3 py-2 text-sm {{ request()->routeIs('account.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">My account</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm text-slate-300 hover:bg-slate-800 hover:text-white">Log out</button>
            </form>
        </div>
    </aside>

    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 md:hidden"></div>

    <div class="min-h-full md:pl-64">
        <div class="sticky top-0 z-20 flex h-14 items-center gap-3 bg-slate-900 px-4 text-white md:hidden">
            <button type="button" id="mobile-menu-button" aria-controls="app-sidebar" aria-expanded="false"
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-300 hover:bg-slate-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-slate-500">
                <span class="sr-only">Toggle navigation menu</span>
                <svg id="mobile-menu-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg id="mobile-menu-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
            <a href="{{ $isSuperAdmin ? route('platform.overview') : route('dashboard') }}" class="text-lg font-bold">Best CPMS</a>
        </div>

    @php $activeSub = $company?->activeSubscription(); @endphp
    @if (! $isSuperAdmin && $company && $company->isOnTrial() && ! $activeSub)
        <div class="border-b border-amber-200 bg-amber-50 py-2 text-center text-sm text-amber-800">
            You're on a free trial until <span class="font-semibold">{{ $company->trial_ends_at->format('j M Y') }}</span> — {{ $company->trialDaysRemaining() }} day(s) left, enough to close your month-end.
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
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
            <h1 class="text-xl font-semibold text-slate-900">@yield('heading', 'Dashboard')</h1>
            <div class="flex flex-wrap items-center gap-2">@yield('actions')</div>
        </div>
    </header>

    <main class="px-4 py-6 sm:px-6">
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
</div>

<script>
    (function () {
        var btn = document.getElementById('mobile-menu-button');
        var sidebar = document.getElementById('app-sidebar');
        var backdrop = document.getElementById('sidebar-backdrop');
        var openIcon = document.getElementById('mobile-menu-open');
        var closeIcon = document.getElementById('mobile-menu-close');
        if (!btn || !sidebar || !backdrop) return;

        function setOpen(open) {
            // Set a positive translate when open. Removing the hide class alone
            // leaves the menu off-screen, because no other transform rule applies below the desktop breakpoint.
            sidebar.classList.toggle('-translate-x-full', !open);
            sidebar.classList.toggle('translate-x-0', open);
            backdrop.classList.toggle('hidden', !open);
            btn.setAttribute('aria-expanded', String(open));
            if (openIcon && closeIcon) {
                openIcon.classList.toggle('hidden', open);
                closeIcon.classList.toggle('hidden', !open);
            }
        }

        btn.addEventListener('click', function () {
            setOpen(sidebar.classList.contains('-translate-x-full'));
        });
        backdrop.addEventListener('click', function () {
            setOpen(false);
        });
    })();
</script>
</body>
</html>
