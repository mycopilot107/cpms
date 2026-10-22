@extends('layouts.app')

@section('title', 'Gantt — Best CPMS')
@section('heading', 'Planning · Gantt')

@section('actions')
    <a href="{{ route('planning.calendar') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Calendar</a>
    <a href="{{ route('milestones.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Milestones</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('planning.gantt') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="project" class="block text-xs font-medium text-slate-500">Project</label>
            <select id="project" name="project" onchange="this.form.submit()"
                    class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All projects</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected($selectedProject && $selectedProject->id === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <noscript><button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Filter</button></noscript>
    </form>

    @if (count($bars) === 0)
        <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
            No scheduled tasks to chart. Add start / due dates to tasks to see them here.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <div class="min-w-[720px]">
                {{-- Month header --}}
                <div class="relative h-8 border-b border-slate-200 bg-slate-50">
                    @foreach ($months as $m)
                        <div class="absolute top-0 flex h-full items-center border-l border-slate-200 pl-1 text-[11px] text-slate-500" style="left: {{ $m['left'] }}%">
                            {{ $m['label'] }}
                        </div>
                    @endforeach
                </div>

                {{-- Rows --}}
                <div class="relative divide-y divide-slate-100">
                    {{-- Today marker --}}
                    @if ($todayLeft !== null)
                        <div class="pointer-events-none absolute inset-y-0 z-10 w-px bg-rose-400" style="left: {{ $todayLeft }}%"></div>
                    @endif

                    @foreach ($bars as $bar)
                        @php $task = $bar['task']; @endphp
                        <div class="grid grid-cols-[220px_1fr] items-center">
                            <div class="truncate px-3 py-2 text-sm">
                                <a href="{{ route('tasks.show', $task) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $task->title }}</a>
                                <div class="truncate text-xs text-slate-400">{{ $task->project?->name }}</div>
                            </div>
                            <div class="relative h-9">
                                <div class="absolute top-1/2 h-4 -translate-y-1/2 rounded {{ $task->isOverdue() ? 'bg-rose-400' : ($task->status === 'completed' ? 'bg-emerald-400' : 'bg-indigo-400') }}"
                                     style="left: {{ $bar['left'] }}%; width: {{ max($bar['width'], 1.5) }}%"
                                     title="{{ $task->start_date?->format('d M Y') }} → {{ $task->due_date?->format('d M Y') }}">
                                    <span class="ml-1 text-[10px] leading-4 text-white">{{ $task->progress }}%</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1"><span class="h-2 w-4 rounded bg-indigo-400"></span> Scheduled</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-4 rounded bg-emerald-400"></span> Completed</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-4 rounded bg-rose-400"></span> Overdue</span>
            <span class="inline-flex items-center gap-1"><span class="h-3 w-px bg-rose-400"></span> Today</span>
            <span>Window: {{ $windowStart->format('d M Y') }} – {{ $windowEnd->format('d M Y') }}</span>
        </div>
    @endif
@endsection
