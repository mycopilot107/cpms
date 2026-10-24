@extends('layouts.public')

@section('title', 'Privacy Policy')
@section('meta_description', 'How Best CPMS collects, uses, and protects your information.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Legal',
        'title'  => 'Privacy Policy',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">
            <p class="text-sm text-slate-500">Last updated: 30 September 2026</p>

            <div class="mt-8 space-y-8 leading-relaxed text-slate-700
                        [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-slate-900
                        [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-1.5 [&_ul]:pl-6">

                <p>This Privacy Policy explains how Best CPMS (&ldquo;we&rdquo;, &ldquo;us&rdquo;) collects, uses, and safeguards information when you use our website and construction project management platform (the &ldquo;Service&rdquo;). By using the Service, you agree to the practices described here.</p>

                <div>
                    <h2>Information we collect</h2>
                    <p>We collect information you provide directly and information generated as you use the Service:</p>
                    <ul>
                        <li><strong>Account information</strong> — your name, email address, company, and role when you register.</li>
                        <li><strong>Project data</strong> — the projects, tasks, budgets, and files you and your team enter into the Service.</li>
                        <li><strong>Contact submissions</strong> — details you send through our contact and feedback forms.</li>
                        <li><strong>Usage and device data</strong> — log data such as IP address, browser type, and pages viewed.</li>
                    </ul>
                </div>

                <div>
                    <h2>How we use information</h2>
                    <p>We use information to provide and improve the Service, authenticate users, process subscriptions, respond to enquiries, maintain security, and meet legal obligations. We do not sell your personal information.</p>
                </div>

                <div>
                    <h2>Multi-company data separation</h2>
                    <p>The Service is multi-tenant. Each company's data is logically isolated: users belonging to one company cannot access another company's projects, costs, or people. Access within a company is further governed by role-based permissions.</p>
                </div>

                <div>
                    <h2>Sharing and disclosure</h2>
                    <p>We share information only with service providers who help us operate the Service (such as hosting and payment processing), when required by law, or to protect our rights and the safety of users. These providers are bound to handle information consistent with this policy.</p>
                </div>

                <div>
                    <h2>Data retention</h2>
                    <p>We retain your information for as long as your account is active or as needed to provide the Service, resolve disputes, and comply with legal obligations. You may request deletion of your account data, subject to records we must keep.</p>
                </div>

                <div>
                    <h2>Security</h2>
                    <p>We use administrative, technical, and organisational measures designed to protect your information. No method of transmission or storage is completely secure, but we work to protect your data and to promptly address issues if they arise.</p>
                </div>

                <div>
                    <h2>Your rights</h2>
                    <p>Depending on your location, you may have rights to access, correct, export, or delete your personal information, and to object to certain processing. To exercise these rights, contact us using the details below.</p>
                </div>

                <div>
                    <h2>Changes to this policy</h2>
                    <p>We may update this policy from time to time. Material changes will be posted on this page with a revised &ldquo;last updated&rdquo; date.</p>
                </div>

                <div>
                    <h2>Contact</h2>
                    <p>Questions about this policy? Email us at <a href="mailto:shobha.solanki107@gmail.com" class="font-semibold text-indigo-600 hover:text-indigo-700">shobha.solanki107@gmail.com</a> or via our <a href="{{ route('contact') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">contact page</a>.</p>
                </div>

                <p class="border-t border-slate-200 pt-6 text-sm text-slate-400">This policy is provided as a general template and should be reviewed by legal counsel before you rely on it.</p>
            </div>
        </div>
    </section>

@endsection
