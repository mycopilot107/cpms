@extends('layouts.app')

@section('title', 'Calendar — Best CPMS')
@section('heading', 'Planning · Calendar')

@section('actions')
    <a href="{{ route('planning.gantt') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Gantt</a>
    <a href="{{ route('milestones.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Milestones</a>
@endsection

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('planning.calendar', ['month' => $prevMonth]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">&larr; Prev</a>
        <h2 class="text-lg font-semibold text-slate-900">{{ $anchor->format('F Y') }}</h2>
        <a href="{{ route('planning.calendar', ['month' => $nextMonth]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Next &rarr;</a>
    </div>

    {{-- Legend --}}
    <div class="mb-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-indigo-500"></span> Task due</span>
        <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Milestone</span>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        {{-- Weekday header (Mon-first) --}}
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
            @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow)
                <div class="px-2 py-2">{{ $dow }}</div>
            @endforeach
        </div>

        {{-- Day cells --}}
        <div class="grid grid-cols-7">
            @for ($i = 0; $i < $leadingBlanks; $i++)
                <div class="min-h-24 border-b border-r border-slate-100 bg-slate-50/50"></div>
            @endfor

            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $isToday = $today->year === $anchor->year && $today->month === $anchor->month && $today->day === $day;
                    $events = $eventsByDay[$day] ?? [];
                @endphp
                <div class="min-h-24 border-b border-r border-slate-100 p-1.5 align-top">
                    <div class="mb-1 text-right text-xs {{ $isToday ? 'font-bold text-indigo-600' : 'text-slate-400' }}">{{ $day }}</div>
                    <div class="space-y-1">
                        @foreach ($events as $event)
                            <a href="{{ $event['url'] }}"
                               class="block truncate rounded px-1 py-0.5 text-[11px] {{ $event['type'] === 'milestone' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}"
                               title="{{ $event['label'] }}{{ $event['project'] ? ' · '.$event['project'] : '' }}">
                                {{ $event['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <p class="mt-3 text-xs text-slate-400">Shows tasks and milestones with a due date in {{ $anchor->format('F Y') }}.</p>
@endsection
