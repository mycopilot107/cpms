<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Currency auto-detect (first visit only). Best CPMS sells worldwide, so a
         visitor outside India defaults to USD. Runs before paint; sets the
         `cpms_currency` cookie and reloads ONCE only when the detected currency
         differs from the server default, so there's no reload loop and an
         explicit choice (cookie already set) is always respected. --}}
    <script>
        (function () {
            try {
                if (document.cookie.split('; ').some(function (c) { return c.indexOf('cpms_currency=') === 0; })) {
                    return; // a choice (user or prior detection) already exists
                }
                var tz = (Intl.DateTimeFormat().resolvedOptions().timeZone || '');
                var lang = (navigator.language || '');
                var isIndia = /Asia\/(Kolkata|Calcutta)/i.test(tz) || /-IN\b/i.test(lang);
                var detected = isIndia ? 'INR' : 'USD';
                document.cookie = 'cpms_currency=' + detected + ';path=/;max-age=31536000;SameSite=Lax';
                if (detected !== @json(\App\Support\Currency::default())) {
                    location.reload();
                }
            } catch (e) { /* detection is best-effort; ignore */ }
        })();
    </script>

    {{-- ============================ SEO ============================ --}}
    @php
        $siteName        = config('seo.site_name', 'Best CPMS');
        $tagline         = config('seo.tagline', 'Construction Project Management Software');
        $pageTitle       = trim($__env->yieldContent('title'));
        $fullTitle       = $pageTitle !== '' ? $pageTitle.' | '.$siteName : $tagline.' | '.$siteName;
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: config('seo.default_description');
        $metaKeywords    = trim($__env->yieldContent('meta_keywords')) ?: config('seo.default_keywords');
        $robots          = trim($__env->yieldContent('robots')) ?: 'index, follow, max-image-preview:large, max-snippet:-1';
        $canonical       = trim($__env->yieldContent('canonical')) ?: url()->current();
        $ogType          = trim($__env->yieldContent('og_type')) ?: 'website';
        $ogImage         = url(config('seo.og_image', '/og-image.png'));
    @endphp

    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Favicons --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ config('seo.locale', 'en_IN') }}">

    {{-- Twitter / X card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @if (config('seo.twitter_handle'))
        <meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
    @endif

    {{-- Organization + WebSite structured data (sitewide) --}}
    @php
        $orgLd = array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => $siteName,
            'legalName'   => config('seo.organization.legal_name'),
            'url'         => url('/'),
            'logo'        => url(config('seo.organization.logo', '/favicon.svg')),
            'description' => config('seo.default_description'),
            'sameAs'      => config('seo.same_as') ?: null,
            'contactPoint' => array_filter([
                '@type'       => 'ContactPoint',
                'contactType' => 'customer support',
                'email'       => config('seo.organization.email'),
                'telephone'   => config('seo.organization.telephone'),
                'areaServed'  => config('seo.organization.country', 'IN'),
            ]) ?: null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);

        // Drop contactPoint if it only carried its @type/contactType skeleton.
        if (isset($orgLd['contactPoint']) && count($orgLd['contactPoint']) <= 3) {
            unset($orgLd['contactPoint']);
        }

        $siteLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => $siteName,
            'url'      => url('/'),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($orgLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($siteLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['"Space Grotesk"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        sans: ['"IBM Plex Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: '"IBM Plex Sans"', ui-sans-serif, system-ui, sans-serif; }
        /* Blueprint drafting grid — used behind heroes and feature bands. */
        .blueprint-grid {
            background-image:
                linear-gradient(to right, rgba(15, 23, 42, 0.045) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.045) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        /* A hairline tick that marks section leads instead of an all-caps eyebrow. */
        .kicker { display: inline-flex; align-items: center; gap: 0.5rem; }
        .kicker::before { content: ""; width: 1.5rem; height: 2px; background: #d97706; display: inline-block; }
    </style>

    {{-- Per-page structured data (SoftwareApplication, FAQPage, BreadcrumbList, …) --}}
    @stack('ld')
</head>
<body class="h-full bg-white text-slate-700 font-sans antialiased">
<div class="flex min-h-full flex-col">

    {{-- ============================ Header ============================ --}}
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-5 py-4 lg:px-8" aria-label="Primary">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display text-lg font-bold text-slate-900">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-indigo-600 font-display text-white">C</span>
                Best CPMS
            </a>

            {{-- Desktop links --}}
            <div class="hidden items-center gap-7 lg:flex">
                <a href="{{ route('features') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Features</a>
                <a href="{{ route('pricing') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Pricing</a>
                <a href="{{ route('case-studies') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Case studies</a>
                <a href="{{ route('reviews') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Reviews</a>
                <a href="{{ route('faq') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">FAQ</a>
            </div>

            {{-- Desktop auth actions --}}
            <div class="hidden items-center gap-3 lg:flex">
                @include('partials.currency-switcher')
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Go to dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600">Start free trial</a>
                @endauth
            </div>

            {{-- Mobile toggle --}}
            <button type="button" id="navToggle" class="inline-flex items-center justify-center rounded-md p-2 text-slate-700 lg:hidden" aria-label="Open menu" aria-expanded="false">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
            </button>
        </nav>

        {{-- Mobile panel --}}
        <div id="navPanel" class="hidden border-t border-slate-200 bg-white lg:hidden">
            <div class="space-y-1 px-5 py-4">
                <a href="{{ route('features') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Features</a>
                <a href="{{ route('pricing') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Pricing</a>
                <a href="{{ route('case-studies') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Case studies</a>
                <a href="{{ route('reviews') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Reviews</a>
                <a href="{{ route('faq') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">FAQ</a>
                <a href="{{ route('contact') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Contact</a>
                <div class="mt-3 flex items-center justify-between border-t border-slate-100 px-3 pt-3">
                    <span class="text-sm font-medium text-slate-500">Currency</span>
                    @include('partials.currency-switcher')
                </div>
                <div class="mt-3 border-t border-slate-100 pt-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block rounded-md bg-slate-900 px-3 py-2 text-center text-base font-semibold text-white">Go to dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50">Log in</a>
                        <a href="{{ route('register') }}" class="mt-1 block rounded-md bg-amber-500 px-3 py-2 text-center text-base font-semibold text-white">Start free trial</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- ============================ Content ============================ --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ============================ Footer ============================ --}}
    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-5 py-14 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-6">
                {{-- Brand blurb --}}
                <div class="lg:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display text-lg font-bold text-slate-900">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-indigo-600 font-display text-white">C</span>
                        Best CPMS
                    </a>
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">Plan, cost, and track construction projects from one workspace.</p>

                    {{-- Social profiles. Driven by config/social.php; empty URLs are dropped. --}}
                    @php($socialLinks = array_filter(config('social.links', [])))
                    @if (! empty($socialLinks))
                        <div class="mt-5 flex items-center gap-3">
                            @foreach ($socialLinks as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                   aria-label="Best CPMS on {{ ucfirst($network === 'twitter' ? 'X' : $network) }}"
                                   class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-white hover:text-slate-900">
                                    @include('partials.social-icon', ['network' => $network])
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Company --}}
                <div>
                    <h3 class="font-display text-sm font-semibold text-slate-900">Company</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('about') }}" class="text-slate-500 transition hover:text-slate-900">About Us</a></li>
                        <li><a href="{{ route('team') }}" class="text-slate-500 transition hover:text-slate-900">Our Team</a></li>
                        <li><a href="{{ route('careers') }}" class="text-slate-500 transition hover:text-slate-900">Careers</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-500 transition hover:text-slate-900">Contact Us</a></li>
                    </ul>
                </div>

                {{-- Solutions --}}
                <div>
                    <h3 class="font-display text-sm font-semibold text-slate-900">Solutions</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('landing.contractors') }}" class="text-slate-500 transition hover:text-slate-900">For Contractors</a></li>
                        <li><a href="{{ route('landing.builders') }}" class="text-slate-500 transition hover:text-slate-900">For Builders</a></li>
                        <li><a href="{{ route('landing.small-business') }}" class="text-slate-500 transition hover:text-slate-900">For Small Business</a></li>
                        <li><a href="{{ route('landing.developers') }}" class="text-slate-500 transition hover:text-slate-900">For Developers</a></li>
                        <li><a href="{{ route('landing.india') }}" class="text-slate-500 transition hover:text-slate-900">Software in India</a></li>
                        <li><a href="{{ route('landing.project-tracking') }}" class="text-slate-500 transition hover:text-slate-900">Project Tracking</a></li>
                        <li><a href="{{ route('landing.budget') }}" class="text-slate-500 transition hover:text-slate-900">Budget Management</a></li>
                        <li><a href="{{ route('landing.expenses') }}" class="text-slate-500 transition hover:text-slate-900">Expense Tracking</a></li>
                        <li><a href="{{ route('landing.reporting') }}" class="text-slate-500 transition hover:text-slate-900">Project Reporting</a></li>
                    </ul>
                </div>

                {{-- Services --}}
                <div>
                    <h3 class="font-display text-sm font-semibold text-slate-900">Services</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('cpm-software') }}" class="text-slate-500 transition hover:text-slate-900">Construction PM Software</a></li>
                        <li><a href="{{ route('services.planning') }}" class="text-slate-500 transition hover:text-slate-900">Planning &amp; Scheduling</a></li>
                        <li><a href="{{ route('services.cost') }}" class="text-slate-500 transition hover:text-slate-900">Cost &amp; Budget Control</a></li>
                        <li><a href="{{ route('services.field') }}" class="text-slate-500 transition hover:text-slate-900">Field &amp; Progress Tracking</a></li>
                        <li><a href="{{ route('landing.boq') }}" class="text-slate-500 transition hover:text-slate-900">BOQ Software</a></li>
                        <li><a href="{{ route('landing.gantt') }}" class="text-slate-500 transition hover:text-slate-900">Gantt Chart</a></li>
                        <li><a href="{{ route('landing.milestones') }}" class="text-slate-500 transition hover:text-slate-900">Milestone Tracking</a></li>
                        <li><a href="{{ route('landing.payments') }}" class="text-slate-500 transition hover:text-slate-900">Payment Tracking</a></li>
                    </ul>
                </div>

                {{-- Resources --}}
                <div>
                    <h3 class="font-display text-sm font-semibold text-slate-900">Resources</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('blog') }}" class="text-slate-500 transition hover:text-slate-900">Blog</a></li>
                        <li><a href="{{ route('faq') }}" class="text-slate-500 transition hover:text-slate-900">FAQ</a></li>
                        <li><a href="{{ route('case-studies') }}" class="text-slate-500 transition hover:text-slate-900">Case Studies</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h3 class="font-display text-sm font-semibold text-slate-900">Legal</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('privacy') }}" class="text-slate-500 transition hover:text-slate-900">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="text-slate-500 transition hover:text-slate-900">Terms &amp; Conditions</a></li>
                        <li><a href="{{ route('refund') }}" class="text-slate-500 transition hover:text-slate-900">Refund Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">&copy; {{ date('Y') }} Best CPMS. All rights reserved.</p>
                <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                    <a href="{{ route('cookies') }}" class="text-slate-500 transition hover:text-slate-900">Cookie Policy</a>
                    <a href="{{ route('help') }}" class="text-slate-500 transition hover:text-slate-900">Help Center</a>
                    <a href="{{ route('mission') }}" class="text-slate-500 transition hover:text-slate-900">Mission &amp; Vision</a>
                    <a href="{{ route('feedback') }}" class="text-slate-500 transition hover:text-slate-900">Give Feedback</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
    (function () {
        var btn = document.getElementById('navToggle');
        var panel = document.getElementById('navPanel');
        if (btn && panel) {
            btn.addEventListener('click', function () {
                var open = panel.classList.toggle('hidden') === false;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }
    })();
</script>
</body>
</html>
