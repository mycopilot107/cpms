@extends('layouts.public')

@section('title', 'Contact Best CPMS')
@section('meta_description', 'Get in touch with the Best CPMS team — sales, support, or anything else. We reply within one business day.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Contact Us',
        'title'  => 'Talk to a human',
        'lead'   => 'Questions about a plan, migrating a live project, or something else entirely? Send a message and we\'ll get back within one business day.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-12">

                {{-- Form --}}
                <div class="lg:col-span-7">
                    @if (session('status'))
                        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            Please check the highlighted fields and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
                        @csrf

                        {{-- Honeypot: real people leave this empty; it's visually hidden. --}}
                        <div class="hidden" aria-hidden="true">
                            <label>Leave this field empty
                                <input type="text" name="website" tabindex="-1" autocomplete="off">
                            </label>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                    class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                    class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-slate-700">Phone <span class="text-slate-400">(optional)</span></label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                                    class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="company" class="block text-sm font-medium text-slate-700">Company <span class="text-slate-400">(optional)</span></label>
                                <input id="company" name="company" type="text" value="{{ old('company') }}"
                                    class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('company')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700">Subject <span class="text-slate-400">(optional)</span></label>
                            <input id="subject" name="subject" type="text" value="{{ old('subject') }}"
                                class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700">Message</label>
                            <textarea id="message" name="message" rows="5" required
                                class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Send message</button>
                    </form>
                </div>

                {{-- Contact details --}}
                <aside class="lg:col-span-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-7">
                        <h2 class="font-display text-lg font-semibold text-slate-900">Other ways to reach us</h2>
                        <dl class="mt-5 space-y-5 text-sm">
                            <div>
                                <dt class="font-medium text-slate-500">Email</dt>
                                <dd class="mt-1"><a href="mailto:shobha.solanki107@gmail.com" class="font-semibold text-indigo-600 hover:text-indigo-700">shobha.solanki107@gmail.com</a></dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Support</dt>
                                <dd class="mt-1 text-slate-700">Visit the <a href="{{ route('help') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Help Center</a> or browse the <a href="{{ route('faq') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">FAQ</a>.</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Response time</dt>
                                <dd class="mt-1 text-slate-700">Within one business day.</dd>
                            </div>
                        </dl>
                    </div>
                    <p class="mt-4 px-1 text-xs text-slate-400">Your message is stored securely and emailed to our team. We never share it.</p>
                </aside>
            </div>
        </div>
    </section>

@endsection
