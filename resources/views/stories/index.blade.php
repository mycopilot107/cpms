@extends('layouts.app')

@section('title', 'Share your success story')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-0">

    <header class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900">Share your success story</h1>
        <p class="mt-1 text-sm text-slate-600">
            Tell other construction teams how Best CPMS has helped you. Your story is sent to our team for review —
            once approved, it may appear on our homepage and reviews page.
        </p>
    </header>

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

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('stories.store') }}" class="space-y-6">
            @csrf

            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label>Leave this field empty
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Your name</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', auth()->user()->name) }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email <span class="text-slate-400">(optional, not published)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="organisation" class="block text-sm font-medium text-slate-700">Company / organisation</label>
                    <input id="organisation" name="organisation" type="text" value="{{ old('organisation', auth()->user()->company->name ?? '') }}"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('organisation')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700">Your role <span class="text-slate-400">(optional)</span></label>
                    <input id="role" name="role" type="text" value="{{ old('role') }}" placeholder="e.g. Projects Director"
                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('role')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <span class="block text-sm font-medium text-slate-700">Overall rating <span class="text-slate-400">(optional)</span></span>
                <div class="mt-2 flex flex-wrap gap-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" {{ (string) old('rating') === (string) $i ? 'checked' : '' }}>
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-md border border-slate-300 font-display font-semibold text-slate-700 transition peer-checked:border-amber-500 peer-checked:bg-amber-500 peer-checked:text-white hover:border-slate-400">{{ $i }}</span>
                        </label>
                    @endfor
                </div>
                @error('rating')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="story" class="block text-sm font-medium text-slate-700">Your story</label>
                <textarea id="story" name="story" rows="6" required
                    placeholder="What changed after you switched to Best CPMS? Share the before-and-after — the problem you had and the result you got."
                    class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('story') }}</textarea>
                @error('story')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-indigo-600 px-5 py-2.5 font-semibold text-white transition hover:bg-indigo-700">
                    Submit for review
                </button>
                <span class="text-xs text-slate-500">Reviewed before publishing — approved stories may appear on our public site.</span>
            </div>
        </form>
    </div>

    {{-- The user's own past submissions --}}
    @if ($myStories->isNotEmpty())
        <section class="mt-10">
            <h2 class="font-display text-lg font-semibold text-slate-900">Your submissions</h2>
            <div class="mt-4 space-y-3">
                @foreach ($myStories as $s)
                    <div class="rounded-lg border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-slate-500">{{ $s->created_at->format('j M Y') }}</span>
                            @if ($s->isApproved())
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Published</span>
                            @elseif ($s->status === 'rejected')
                                <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Not published</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Awaiting review</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm leading-relaxed text-slate-700">{{ \Illuminate\Support\Str::limit($s->story, 200) }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
