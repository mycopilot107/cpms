{{--
    "Sign in with Google / Facebook" buttons. Shared by the login and register
    pages. The links go to the OAuth redirect route; if a provider has no
    credentials configured, the controller flashes a friendly notice and returns
    the user here. Pass $labelVerb to tune the wording (e.g. 'Sign up' vs 'Continue').
--}}
@php($labelVerb = $labelVerb ?? 'Continue')

<div class="mt-6">
    <div class="relative">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="bg-white px-3 text-xs font-medium uppercase tracking-wide text-slate-400">{{ $labelVerb }} with</span>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
        {{-- Google --}}
        <a href="{{ route('social.redirect', 'google') }}"
           class="inline-flex items-center justify-center gap-2.5 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M23.52 12.273c0-.851-.076-1.67-.218-2.455H12v4.642h6.458a5.52 5.52 0 0 1-2.394 3.622v3.01h3.878c2.27-2.09 3.578-5.166 3.578-8.82z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.956-1.075 7.941-2.908l-3.878-3.01c-1.075.72-2.45 1.144-4.063 1.144-3.125 0-5.77-2.11-6.714-4.947H1.276v3.11A11.997 11.997 0 0 0 12 24z"/>
                <path fill="#FBBC05" d="M5.286 14.279A7.212 7.212 0 0 1 4.909 12c0-.79.136-1.558.377-2.279V6.611H1.276A11.997 11.997 0 0 0 0 12c0 1.936.464 3.769 1.276 5.389l4.01-3.11z"/>
                <path fill="#EA4335" d="M12 4.774c1.762 0 3.343.606 4.586 1.794l3.44-3.44C17.951 1.19 15.235 0 12 0A11.997 11.997 0 0 0 1.276 6.611l4.01 3.11C6.23 6.884 8.875 4.774 12 4.774z"/>
            </svg>
            Google
        </a>

        {{-- Facebook --}}
        <a href="{{ route('social.redirect', 'facebook') }}"
           class="inline-flex items-center justify-center gap-2.5 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Facebook
        </a>
    </div>
</div>
