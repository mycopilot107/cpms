<!DOCTYPE html>
@php
    $title = $project->name.' — weekly update '.$link->weekLabel();
    $summary = $progress.'% complete · '.$completedThisWeek->count().' task(s) finished this week'
        .($link->include_labour && $labour['days_reported'] > 0 ? ' · '.$labour['labour_days'].' labour-days on site' : '');
    $firstPhoto = $photos->first();
    $photoUrl = fn (array $photo, bool $thumb = true) => route('weekly-update.photo', array_filter([
        'token' => $link->token,
        'kind' => $photo['kind'],
        'photo' => $photo['id'],
        'size' => $thumb ? 'thumb' : null,
    ]));
    $milestoneStyle = [
        'completed' => ['Done', 'bg-emerald-50 text-emerald-700'],
        'in_progress' => ['In progress', 'bg-indigo-50 text-indigo-700'],
        'delayed' => ['Delayed', 'bg-rose-50 text-rose-700'],
        'pending' => ['Not started', 'bg-slate-100 text-slate-600'],
    ];
    $weekDays = collect(range(0, 6))->map(fn ($i) => $link->week_start->copy()->addDays($i));
@endphp
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }}</title>
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $summary }}">
    <meta property="og:type" content="article">
    @if ($firstPhoto)
        <meta property="og:image" content="{{ $photoUrl($firstPhoto) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            section { break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
<div class="mx-auto max-w-3xl px-4 py-6 sm:py-10">

    {{-- Header --}}
    <header class="rounded-2xl bg-slate-900 p-6 text-white">
        <p class="text-sm text-slate-300">{{ $companyName }}</p>
        <h1 class="mt-1 text-2xl font-bold leading-tight">{{ $project->name }}</h1>
        <p class="mt-1 text-sm text-slate-300">
            Weekly update · {{ $link->weekLabel() }}
            @if ($project->project_code) · {{ $project->project_code }} @endif
        </p>
        <div class="mt-5">
            <div class="flex items-end justify-between text-sm">
                <span class="text-slate-300">Overall progress</span>
                <span class="text-2xl font-bold">{{ $progress }}%</span>
            </div>
            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-700">
                <div class="h-3 rounded-full bg-emerald-400" style="width: {{ $progress }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-400">{{ $taskCounts['completed'] }} of {{ $taskCounts['total'] }} tasks complete</p>
        </div>
    </header>

    <div class="mt-5 grid grid-cols-3 gap-3 text-center">
        <div class="rounded-xl bg-white p-3">
            <p class="text-2xl font-bold text-emerald-700">{{ $completedThisWeek->count() }}</p>
            <p class="text-xs text-slate-500">finished this week</p>
        </div>
        <div class="rounded-xl bg-white p-3">
            <p class="text-2xl font-bold text-slate-900">{{ $inProgress->count() }}</p>
            <p class="text-xs text-slate-500">in progress</p>
        </div>
        <div class="rounded-xl bg-white p-3">
            <p class="text-2xl font-bold {{ $overdue->isNotEmpty() ? 'text-rose-600' : 'text-slate-900' }}">{{ $overdue->count() }}</p>
            <p class="text-xs text-slate-500">running late</p>
        </div>
    </div>

    @if ($link->message)
        <section class="mt-5 rounded-xl border-l-4 border-emerald-500 bg-white p-5">
            <p class="whitespace-pre-line text-sm leading-relaxed text-slate-800">{{ $link->message }}</p>
            @if ($sharedBy)
                <p class="mt-2 text-xs font-medium text-slate-500">— {{ $sharedBy->name }}</p>
            @endif
        </section>
    @endif

    {{-- Done this week --}}
    <section class="mt-5 rounded-xl bg-white p-5">
        <h2 class="text-base font-semibold text-slate-900">Finished this week</h2>
        @if ($completedThisWeek->isEmpty())
            <p class="mt-2 text-sm text-slate-500">No tasks were marked finished this week.</p>
        @else
            <ul class="mt-3 space-y-2">
                @foreach ($completedThisWeek as $task)
                    <li class="flex items-start gap-2 text-sm">
                        <span class="mt-0.5 text-emerald-600">✓</span>
                        <span class="flex-1 text-slate-800">{{ $task->title }}</span>
                        <span class="shrink-0 text-xs text-slate-400">{{ $task->completed_at?->format('D d M') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Site diary --}}
    <section class="mt-5 rounded-xl bg-white p-5">
        <h2 class="text-base font-semibold text-slate-900">Day by day on site</h2>
        @if ($days->isEmpty())
            <p class="mt-2 text-sm text-slate-500">No daily site reports were filed this week.</p>
        @else
            @if ($link->include_labour && $labour['days_reported'] > 0)
                <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                    <span class="font-semibold">{{ number_format($labour['labour_days']) }} labour-days</span> over {{ $labour['days_reported'] }} working day(s),
                    about {{ $labour['average'] }} people a day.
                    @if ($labour['by_trade'])
                        <span class="block text-xs text-slate-500">{{ collect($labour['by_trade'])->map(fn ($count, $trade) => $trade.' '.$count)->implode(' · ') }}</span>
                    @endif
                </div>
            @endif
            <ol class="mt-4 space-y-4">
                @foreach ($weekDays as $day)
                    @php($entries = $days->get($day->toDateString()))
                    @if ($entries)
                        <li class="border-l-2 border-indigo-200 pl-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-900">{{ $day->format('l, d M') }}</p>
                                @if ($link->include_labour)
                                    <p class="text-xs font-medium text-slate-500">{{ $entries->max('labour_count') }} labour</p>
                                @endif
                            </div>
                            @foreach ($entries as $entry)
                                <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $entry->work_done }}</p>
                                @if ($link->include_notes && $entry->notes)
                                    <p class="mt-1 text-sm text-amber-700"><span class="font-semibold">Note:</span> {{ $entry->notes }}</p>
                                @endif
                            @endforeach
                        </li>
                    @endif
                @endforeach
            </ol>
        @endif
    </section>

    {{-- Photos --}}
    @if ($link->include_photos && $photos->isNotEmpty())
        <section class="mt-5 rounded-xl bg-white p-5">
            <h2 class="text-base font-semibold text-slate-900">Site photos <span class="font-normal text-slate-400">({{ $photos->count() }})</span></h2>
            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($photos as $photo)
                    <a href="{{ $photoUrl($photo, false) }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-lg bg-slate-100">
                        <img src="{{ $photoUrl($photo) }}" alt="{{ $photo['caption'] ?: $photo['label'] }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                        <p class="truncate px-2 py-1 text-xs text-slate-600">{{ $photo['caption'] ?: $photo['label'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Milestones --}}
    @if ($milestones->isNotEmpty())
        <section class="mt-5 rounded-xl bg-white p-5">
            <h2 class="text-base font-semibold text-slate-900">Milestones</h2>
            <ul class="mt-3 divide-y divide-slate-100">
                @foreach ($milestones as $milestone)
                    @php([$label, $class] = $milestoneStyle[$milestone->status] ?? [ucfirst($milestone->status), 'bg-slate-100 text-slate-600'])
                    <li class="flex items-center justify-between gap-3 py-2 text-sm">
                        <span class="text-slate-800">{{ $milestone->name }}</span>
                        <span class="flex shrink-0 items-center gap-2">
                            @if ($milestone->due_date)<span class="text-xs text-slate-400">{{ $milestone->due_date->format('d M') }}</span>@endif
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $class }}">{{ $label }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- In progress / late / next --}}
    @foreach ([
        ['In progress', $inProgress, 'Nothing in progress right now.'],
        ['Running late', $overdue, null],
        ['Planned for next week', $nextWeek, null],
    ] as [$heading, $list, $emptyText])
        @if ($list->isNotEmpty() || $emptyText)
            <section class="mt-5 rounded-xl bg-white p-5">
                <h2 class="text-base font-semibold {{ $heading === 'Running late' ? 'text-rose-700' : 'text-slate-900' }}">{{ $heading }}</h2>
                @if ($list->isEmpty())
                    <p class="mt-2 text-sm text-slate-500">{{ $emptyText }}</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($list as $task)
                            <li class="text-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-slate-800">{{ $task->title }}</span>
                                    @if ($task->due_date)
                                        <span class="shrink-0 text-xs {{ $heading === 'Running late' ? 'font-semibold text-rose-600' : 'text-slate-400' }}">due {{ $task->due_date->format('d M') }}</span>
                                    @endif
                                </div>
                                @if ($heading !== 'Planned for next week')
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-1.5 rounded-full {{ $heading === 'Running late' ? 'bg-rose-400' : 'bg-indigo-500' }}" style="width: {{ $task->progress }}%"></div>
                                        </div>
                                        <span class="w-9 text-right text-xs text-slate-500">{{ $task->progress }}%</span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif
    @endforeach

    <footer class="mt-8 flex flex-col items-center gap-3 pb-6 text-center text-xs text-slate-500">
        <p>
            Shared by {{ $sharedBy?->name ?? $companyName }}@if ($sharedBy?->email) · <a href="mailto:{{ $sharedBy->email }}" class="underline">{{ $sharedBy->email }}</a>@endif
            @if ($sharedBy?->phone) · <a href="tel:{{ $sharedBy->phone }}" class="underline">{{ $sharedBy->phone }}</a>@endif
        </p>
        <button type="button" onclick="window.print()" class="no-print rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Print or save as PDF</button>
        <p>Made with <a href="{{ route('home') }}" class="font-semibold text-indigo-600">Best CPMS</a></p>
    </footer>
</div>
</body>
</html>
