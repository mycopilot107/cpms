@extends('layouts.app')

@section('title', 'Success-story moderation')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-0">

    <header class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900">Success-story moderation</h1>
            <p class="mt-1 text-sm text-slate-600">Approve, feature, or remove customer stories submitted from the app.</p>
        </div>
    </header>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    {{-- Status filter --}}
    @php
        $tabs = [
            'pending'  => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'all'      => 'All',
        ];
    @endphp
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.stories.index', ['status' => $key]) }}"
               class="rounded-md px-3 py-1.5 text-sm font-medium transition
                      {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
                <span class="ml-1 rounded-full bg-black/10 px-2 py-0.5 text-xs">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    @if ($stories->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <p class="font-medium text-slate-700">No stories in this queue.</p>
            <p class="mt-1 text-sm text-slate-500">When someone shares a story from the app, it will appear here for review.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($stories as $story)
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-display text-sm font-semibold text-indigo-700">{{ $story->initials() }}</span>
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ $story->name }}
                                    @if ($story->role)<span class="font-normal text-slate-500">— {{ $story->role }}</span>@endif
                                </p>
                                <p class="text-sm text-slate-500">
                                    {{ $story->organisation ?: '—' }}
                                    @if ($story->company) · <span class="text-slate-400">company: {{ $story->company->name }}</span>@endif
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($story->rating)
                                <span class="text-sm text-amber-500" aria-label="{{ $story->rating }} out of 5">{!! str_repeat('★', $story->rating) . str_repeat('☆', 5 - $story->rating) !!}</span>
                            @endif
                            @if ($story->is_featured)
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Featured</span>
                            @endif
                            @php
                                $badge = [
                                    'pending'  => 'bg-amber-50 text-amber-700',
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-rose-50 text-rose-700',
                                ][$story->status] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ ucfirst($story->status) }}</span>
                        </div>
                    </div>

                    <blockquote class="mt-4 leading-relaxed text-slate-700">&ldquo;{{ $story->story }}&rdquo;</blockquote>

                    <p class="mt-3 text-xs text-slate-400">
                        Submitted {{ $story->created_at->format('j M Y, H:i') }}
                        @if ($story->email) · {{ $story->email }} @endif
                        @if ($story->submitter) · by {{ $story->submitter->name }} @endif
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                        @if (! $story->isApproved())
                            <form method="POST" action="{{ route('admin.stories.approve', $story) }}">
                                @csrf @method('PATCH')
                                <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Approve</button>
                            </form>
                        @endif

                        @if ($story->isApproved())
                            <form method="POST" action="{{ route('admin.stories.feature', $story) }}">
                                @csrf @method('PATCH')
                                <button class="rounded-md px-4 py-2 text-sm font-semibold transition
                                               {{ $story->is_featured ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-amber-500 text-white hover:bg-amber-600' }}">
                                    {{ $story->is_featured ? 'Remove from homepage' : 'Feature on homepage' }}
                                </button>
                            </form>
                        @endif

                        @if ($story->status !== 'rejected')
                            <form method="POST" action="{{ route('admin.stories.reject', $story) }}">
                                @csrf @method('PATCH')
                                <button class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 transition hover:bg-slate-50">Reject</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.stories.destroy', $story) }}"
                              onsubmit="return confirm('Permanently delete this story? This cannot be undone.');">
                            @csrf @method('DELETE')
                            <button class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-rose-700 ring-1 ring-rose-200 transition hover:bg-rose-50">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $stories->links() }}</div>
    @endif

</div>
@endsection
