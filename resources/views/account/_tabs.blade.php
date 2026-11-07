{{-- My account tabs, shared by the profile and password pages. --}}
<nav class="mb-6 flex gap-2 border-b border-slate-200" aria-label="My account">
    @foreach ([
        ['account.profile', 'Profile'],
        ['account.password', 'Password'],
    ] as [$routeName, $label])
        <a href="{{ route($routeName) }}"
           class="-mb-px border-b-2 px-3 py-2 text-sm font-medium {{ request()->routeIs($routeName) ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</nav>
