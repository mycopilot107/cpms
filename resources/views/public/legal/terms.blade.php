@extends('layouts.public')

@section('title', 'Terms & Conditions')
@section('meta_description', 'The terms that govern your use of the Best CPMS platform and website.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Legal',
        'title'  => 'Terms & Conditions',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">
            <p class="text-sm text-slate-500">Last updated: 7 October 2026</p>

            <div class="mt-8 space-y-8 leading-relaxed text-slate-700
                        [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-slate-900
                        [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-1.5 [&_ul]:pl-6">

                <p>These Terms &amp; Conditions (&ldquo;Terms&rdquo;) govern your access to and use of the Best CPMS website and platform (the &ldquo;Service&rdquo;). By creating an account or using the Service, you agree to these Terms. If you are using the Service on behalf of a company, you agree on its behalf.</p>

                <div>
                    <h2>Accounts</h2>
                    <p>You are responsible for maintaining the confidentiality of your account credentials and for all activity under your account. You must provide accurate information and keep it up to date. You must be authorised to bind your company if you register on its behalf.</p>
                </div>

                <div>
                    <h2>Acceptable use</h2>
                    <p>You agree not to misuse the Service. In particular, you will not:</p>
                    <ul>
                        <li>attempt to access data belonging to another company or user;</li>
                        <li>probe, scan, or test the vulnerability of the Service or breach its security;</li>
                        <li>upload unlawful, infringing, or malicious content;</li>
                        <li>use the Service to build a competing product or resell it without permission.</li>
                    </ul>
                </div>

                <div>
                    <h2>Subscriptions and billing</h2>
                    <p>Paid plans are billed per user, per month, for the term you select, based on the pricing in effect at purchase. Trials convert to paid subscriptions only when you choose a plan. Fees are payable in advance and, except as stated in our Refund Policy, are non-refundable.</p>
                </div>

                <div>
                    <h2>Your content</h2>
                    <p>You retain all rights to the data and content you enter into the Service. You grant us a limited licence to host and process it solely to provide the Service. You are responsible for the accuracy and legality of your content.</p>
                </div>

                <div>
                    <h2>Availability</h2>
                    <p>We work to keep the Service available and reliable but do not guarantee uninterrupted access. We may modify, suspend, or discontinue features with reasonable notice where practical.</p>
                </div>

                <div>
                    <h2>Disclaimers and limitation of liability</h2>
                    <p>The Service is provided &ldquo;as is&rdquo; without warranties of any kind to the extent permitted by law. To the maximum extent permitted by law, our aggregate liability arising from the Service is limited to the fees you paid in the twelve months preceding the claim.</p>
                </div>

                <div>
                    <h2>Termination</h2>
                    <p>You may stop using the Service at any time. We may suspend or terminate access if you breach these Terms. On termination, your right to use the Service ends; provisions that by their nature should survive will survive.</p>
                </div>

                <div>
                    <h2>Changes to these Terms</h2>
                    <p>We may update these Terms from time to time. We will post changes on this page and update the &ldquo;last updated&rdquo; date; continued use after changes take effect constitutes acceptance.</p>
                </div>

                <div>
                    <h2>Contact</h2>
                    <p>Questions about these Terms? Email <a href="mailto:abc@gmail.com" class="font-semibold text-indigo-600 hover:text-indigo-700">abc@gmail.com</a> or use our <a href="{{ route('contact') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">contact page</a>.</p>
                </div>

                <p class="border-t border-slate-200 pt-6 text-sm text-slate-400">These terms are provided as a general template and should be reviewed by legal counsel before you rely on them.</p>
            </div>
        </div>
    </section>

@endsection
