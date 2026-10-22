{{--
    Android field-app download. Shared by the login and register pages.
    Links to the public app page, which serves the APK when published.
--}}
<a href="{{ route('app.download-page') }}"
   aria-label="Download the Best CPMS Android app"
   class="mt-4 flex items-center gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-left transition hover:border-emerald-300 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
    <span class="flex h-10 w-10 flex-none items-center justify-center rounded-lg bg-[#3DDC84] text-white shadow-sm">
        @include('partials.android-icon', ['class' => 'h-6 w-6'])
    </span>
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-slate-900">Download the Android app</span>
        <span class="block text-xs text-slate-600">For Android phones — field staff report tasks from site</span>
    </span>
    <svg class="ml-auto h-4 w-4 flex-none text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="m9 18 6-6-6-6"/>
    </svg>
</a>
