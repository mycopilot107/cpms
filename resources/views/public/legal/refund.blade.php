@extends('layouts.public')

@section('title', 'Refund & Cancellation Policy')
@section('meta_description', 'How cancellations and refunds work for Best CPMS subscriptions.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Legal',
        'title'  => 'Refund & Cancellation Policy',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">
            <p class="text-sm text-slate-500">Last updated: 30 September 2026</p>

            <div class="mt-8 space-y-8 leading-relaxed text-slate-700
                        [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-slate-900
                        [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-1.5 [&_ul]:pl-6">

                <p>This policy explains how cancellations and refunds work for Best CPMS subscriptions. It applies alongside our Terms &amp; Conditions.</p>

                <div>
                    <h2>Free trial</h2>
                    <p>Every account begins with a free trial. No payment is taken during the trial, and you can stop at any time before it ends without being charged.</p>
                </div>

                <div>
                    <h2>Cancelling your subscription</h2>
                    <p>You can cancel your subscription at any time from your account. When you cancel, your plan remains active until the end of the current paid term, after which it will not renew. Your data remains accessible until the term ends.</p>
                </div>

                <div>
                    <h2>Refunds</h2>
                    <p>Subscription fees are billed in advance for the full term and are generally non-refundable, except:</p>
                    <ul>
                        <li>where required by applicable law;</li>
                        <li>if you cancel within 7 days of the initial purchase and have not made significant use of paid features (a &ldquo;cooling-off&rdquo; period);</li>
                        <li>in cases of a confirmed billing error on our side.</li>
                    </ul>
                    <p>Approved refunds are issued to the original payment method. Partial-term refunds for mid-term cancellations are not provided unless stated above or required by law.</p>
                </div>

                <div>
                    <h2>Seat changes</h2>
                    <p>Adding seats mid-term is charged pro rata for the remainder of the term. Reducing seats takes effect at the next renewal; we do not refund unused seats within a term.</p>
                </div>

                <div>
                    <h2>How to request a refund</h2>
                    <p>To request a refund or ask about cancellation, email <a href="mailto:shobha.solanki107@gmail.com" class="font-semibold text-indigo-600 hover:text-indigo-700">shobha.solanki107@gmail.com</a> with your account details. We aim to respond within one business day.</p>
                </div>

                <div>
                    <h2>Changes to this policy</h2>
                    <p>We may update this policy from time to time. Changes will be posted here with a revised &ldquo;last updated&rdquo; date.</p>
                </div>

                <p class="border-t border-slate-200 pt-6 text-sm text-slate-400">This policy is provided as a general template and should be reviewed by legal counsel before you rely on it.</p>
            </div>
        </div>
    </section>

@endsection
