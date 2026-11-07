@extends('layouts.app')

@section('title', 'Messages — platform')
@section('heading', 'Messages')

@php
    $tabs = ['all' => 'All', 'unread' => 'Unread', 'contact' => 'Contact', 'feedback' => 'Feedback'];
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('platform.messages.index', array_filter(['type' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $type === $key ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50' }}">
                    {{ $label }} ({{ $counts[$key] }})
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('platform.messages.index') }}" class="flex gap-2">
            @if ($type !== 'all')<input type="hidden" name="type" value="{{ $type }}">@endif
            <input type="search" name="q" value="{{ $search }}" placeholder="Name, email, company…"
                   class="w-64 rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Search</button>
        </form>
    </div>

    <div class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        @forelse ($messages as $message)
            <a href="{{ route('platform.messages.show', $message) }}" class="flex items-start gap-4 px-5 py-4 hover:bg-slate-50">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $message->read_at ? 'bg-transparent' : 'bg-indigo-500' }}" title="{{ $message->read_at ? 'Read' : 'Unread' }}"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="{{ $message->read_at ? 'font-medium' : 'font-semibold' }} text-slate-900">{{ $message->name }}</span>
                        <span class="text-xs text-slate-500">{{ $message->email }}</span>
                        <span class="rounded px-1.5 py-0.5 text-[11px] font-semibold {{ $message->isFeedback() ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700' }}">{{ $message->isFeedback() ? 'Feedback' : 'Contact' }}</span>
                        @if ($message->rating)
                            <span class="text-xs text-amber-600">{{ str_repeat('★', $message->rating) }}{{ str_repeat('☆', 5 - $message->rating) }}</span>
                        @endif
                    </div>
                    @if ($message->subject)
                        <p class="mt-0.5 text-sm font-medium text-slate-700">{{ $message->subject }}</p>
                    @endif
                    <p class="mt-0.5 truncate text-sm text-slate-500">{{ $message->message }}</p>
                </div>
                <span class="shrink-0 text-xs text-slate-400">{{ $message->created_at?->format('d M Y') }}</span>
            </a>
        @empty
            <p class="px-5 py-12 text-center text-sm text-slate-400">No messages.</p>
        @endforelse
    </div>

    @if ($messages->hasPages())
        <div>{{ $messages->links() }}</div>
    @endif
</div>
@endsection
