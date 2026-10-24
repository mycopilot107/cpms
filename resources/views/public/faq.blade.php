@extends('layouts.public')

@section('title', 'Construction Project Management FAQ')
@section('meta_description', 'Common questions about Best CPMS construction project management software — free trial, pricing, BOQ and costs, mobile use, data security, and getting your team started.')
@section('meta_keywords', 'construction project management software faq, construction management software questions, best cpms pricing, construction software free trial')

@php
    // Single source for both the on-page accordion and the FAQPage structured
    // data below — keep the two in sync by only editing here.
    $faqs = [
        ['Getting started', [
            ['How long does setup take?', 'Most teams load their first live project in an afternoon. There\'s no installation — Best CPMS runs in the browser, and site teams can use it on their phones straight away.'],
            ['Do you offer a free trial?', 'Yes. Every account starts with a 15-day free trial with no card required. You only enter billing details when you decide to subscribe.'],
            ['Can we import existing projects?', 'You can set up projects, milestones, and a BOQ manually, and our team will help you migrate live projects during onboarding.'],
        ]],
        ['Pricing & billing', [
            ['How does pricing work?', 'You pay per user, per month — the whole platform is included. The total is simply your number of seats × your term × the per-user price. See the pricing page for a live estimate.'],
            ['Is there a minimum?', 'Subscriptions start at a minimum number of seats. You can add more seats at any time as your team grows.'],
            ['What happens when my trial ends?', 'Your data stays put. You choose a plan to keep working; nothing is deleted when a trial lapses.'],
        ]],
        ['Using Best CPMS outside India', [
            ['Can companies outside India use Best CPMS?', 'Yes. When you sign up you choose your country and the currency your project costs are recorded in — for example US dollars, pounds, euros, or UAE dirhams. Budgets, BOQ, expenses, payments, and reports then show that currency. Owners and admins can change it later under Company settings.'],
            ['Which currency is the subscription billed in?', 'Prices can be shown in rupees or US dollars. Checkout runs through Razorpay and is billed in Indian rupees unless US-dollar billing is switched on for your account, in which case companies that use dollars are billed in dollars. Cards issued outside India can be used where Razorpay accepts them; your bank converts the rupee amount.'],
            ['Can I record a VAT or other tax number?', 'Yes. Company settings has a Tax ID field for a GST, VAT, TRN, EIN, or similar registration number.'],
            ['Which languages are supported?', 'The website and the app are in English today.'],
        ]],
        ['Security & data', [
            ['Is my company\'s data separate from other companies?', 'Yes. Each company\'s data is fully isolated. A user in one company can never see or access another company\'s projects, costs, or people.'],
            ['Who in my team can see what?', 'Access is role-based. Owners and admins manage the account and billing; members see the projects and data their role allows.'],
            ['Where is my data stored?', 'Your data is stored securely and backed up regularly. See our privacy and cookie policies for the details.'],
        ]],
    ];
@endphp

@push('ld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faqs)->flatMap(fn ($group) => collect($group[1])->map(fn ($qa) => [
        '@type' => 'Question',
        'name' => $qa[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
    ]))->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Common questions',
        'title'  => 'Answers, before you ask',
        'lead'   => 'The things teams most often want to know. Can\'t find it? The help center and our team are a click away.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="space-y-12">
                @foreach ($faqs as $group)
                    <div>
                        <h2 class="font-display text-xl font-bold text-slate-900">{{ $group[0] }}</h2>
                        <div class="mt-4 divide-y divide-slate-200 border-t border-slate-200">
                            @foreach ($group[1] as $qa)
                                <details class="group py-4">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium text-slate-900">
                                        {{ $qa[0] }}
                                        <span class="ml-2 shrink-0 text-slate-400 transition group-open:rotate-45" aria-hidden="true">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        </span>
                                    </summary>
                                    <p class="mt-3 leading-relaxed text-slate-600">{{ $qa[1] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-14 rounded-xl border border-slate-200 bg-slate-50 p-6 text-center">
                <p class="text-slate-700">Still have a question?</p>
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('help') }}" class="rounded-md border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:border-slate-400">Visit help center</a>
                    <a href="{{ route('contact') }}" class="rounded-md bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Contact us</a>
                </div>
            </div>
        </div>
    </section>

@endsection
