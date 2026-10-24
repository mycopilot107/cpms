@extends('layouts.public')

@section('title', 'Cookie Policy')
@section('meta_description', 'How Best CPMS uses cookies and similar technologies.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Legal',
        'title'  => 'Cookie Policy',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">
            <p class="text-sm text-slate-500">Last updated: 30 September 2026</p>

            <div class="mt-8 space-y-8 leading-relaxed text-slate-700
                        [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-slate-900
                        [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-1.5 [&_ul]:pl-6">

                <p>This Cookie Policy explains how Best CPMS uses cookies and similar technologies when you visit our website and use the Service.</p>

                <div>
                    <h2>What cookies are</h2>
                    <p>Cookies are small text files stored on your device that help websites function and remember information about your visit. Similar technologies include local storage and pixels.</p>
                </div>

                <div>
                    <h2>How we use cookies</h2>
                    <ul>
                        <li><strong>Essential</strong> — required for the Service to work, including signing you in and keeping your session secure. These cannot be switched off.</li>
                        <li><strong>Preferences</strong> — remember choices such as display settings so the Service behaves the way you expect.</li>
                        <li><strong>Analytics</strong> — help us understand how the Service is used so we can improve it. These are aggregated and do not identify you personally.</li>
                    </ul>
                </div>

                <div>
                    <h2>Managing cookies</h2>
                    <p>Most browsers let you refuse or delete cookies through their settings. Blocking essential cookies may prevent parts of the Service from working — for example, staying signed in. Analytics and preference cookies can be declined without affecting core functionality.</p>
                </div>

                <div>
                    <h2>Third-party cookies</h2>
                    <p>Some cookies may be set by service providers we use, such as our payment processor or analytics provider. Their use of cookies is governed by their own policies.</p>
                </div>

                <div>
                    <h2>Changes to this policy</h2>
                    <p>We may update this Cookie Policy from time to time. Changes will be posted on this page with a revised &ldquo;last updated&rdquo; date.</p>
                </div>

                <div>
                    <h2>Contact</h2>
                    <p>Questions about our use of cookies? Email <a href="mailto:shobha.solanki107@gmail.com" class="font-semibold text-indigo-600 hover:text-indigo-700">shobha.solanki107@gmail.com</a> or see our <a href="{{ route('privacy') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Privacy Policy</a>.</p>
                </div>

                <p class="border-t border-slate-200 pt-6 text-sm text-slate-400">This policy is provided as a general template and should be reviewed by legal counsel before you rely on it.</p>
            </div>
        </div>
    </section>

@endsection
