@extends('layouts.app')

@section('title', 'Message from '.$message->name.' — platform')
@section('heading', $message->isFeedback() ? 'Feedback' : 'Contact message')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('platform.messages.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">&larr; All messages</a>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-lg font-semibold text-slate-900">{{ $message->name }}</p>
                <p class="text-sm text-slate-500">{{ $message->email }}@if ($message->phone) · {{ $message->phone }}@endif</p>
                @if ($message->company)
                    <p class="text-sm text-slate-500">{{ $message->company }}</p>
                @endif
            </div>
            <div class="text-right text-xs text-slate-400">
                <p>{{ $message->created_at?->format('d M Y, H:i') }}</p>
                @if ($message->ip_address)<p>IP {{ $message->ip_address }}</p>@endif
            </div>
        </div>

        @if ($message->rating)
            <p class="mt-4 text-lg text-amber-500" aria-label="{{ $message->rating }} out of 5">{{ str_repeat('★', $message->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $message->rating) }}</span></p>
        @endif

        @if ($message->subject)
            <h2 class="mt-4 font-semibold text-slate-800">{{ $message->subject }}</h2>
        @endif
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $message->message }}</p>

        <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message to Best CPMS')) }}"
           class="mt-6 inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Reply by email</a>
    </section>
</div>
@endsection
