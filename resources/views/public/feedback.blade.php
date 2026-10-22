@extends('layouts.public')

@section('title', 'Customer Feedback')
@section('meta_description', 'Tell us what\'s working and what isn\'t. Your feedback shapes what we build next in Best CPMS.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Customer Feedback',
        'title'  => 'Tell us how we\'re doing',
        'lead'   => 'Good, bad, or somewhere in between — your feedback goes straight to the team and shapes what we build next.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-2xl px-5 py-16 sm:py-20 lg:px-8">
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

            <form method="POST" action="{{ route('feedback.store') }}" class="space-y-6">
                @csrf

                {{-- Honeypot --}}
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
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-slate-700">Company <span class="text-slate-400">(optional)</span></label>
                    <input id="company" name="company" type="text" value="{{ old('company') }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('company')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                {{-- Rating: 1–5 radio pills --}}
                <div>
                    <span class="block text-sm font-medium text-slate-700">Overall rating</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" {{ (string) old('rating') === (string) $i ? 'checked' : '' }} required>
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-md border border-slate-300 font-display font-semibold text-slate-700 transition peer-checked:border-amber-500 peer-checked:bg-amber-500 peer-checked:text-white hover:border-slate-400">{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                    <p class="mt-2 text-xs text-slate-500">1 = poor · 5 = excellent</p>
                    @error('rating')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700">Your feedback</label>
                    <textarea id="message" name="message" rows="5" required
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Send feedback</button>
            </form>

            <p class="mt-6 text-sm text-slate-500">Prefer to browse what others say? Read our <a href="{{ route('reviews') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">customer reviews</a>.</p>
        </div>
    </section>

@endsection
