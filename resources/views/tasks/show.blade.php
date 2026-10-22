@extends('layouts.app')

@section('title', $task->title.' — Best CPMS')
@section('heading', 'Task')

@section('actions')
    @can('update', $task)
        <a href="{{ route('tasks.edit', $task) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Edit</a>
    @endcan
    @can('delete', $task)
        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-md border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button>
        </form>
    @endcan
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $task->title }}</h2>
                    <div class="flex items-center gap-2">
                        @include('partials.task-priority', ['priority' => $task->priority])
                        @include('partials.task-status', ['status' => $task->status])
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-400">Project</dt><dd><a href="{{ route('projects.show', $task->project) }}" class="text-indigo-600 hover:text-indigo-500">{{ $task->project?->name }}</a></dd></div>
                    <div><dt class="text-slate-400">Assignee</dt><dd class="text-slate-800">{{ $task->assignee?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt class="text-slate-400">Start date</dt><dd class="text-slate-800">{{ $task->start_date?->format('d M Y') ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">Due date</dt><dd class="{{ $task->isOverdue() ? 'font-medium text-red-600' : 'text-slate-800' }}">{{ $task->due_date?->format('d M Y') ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">Estimated hours</dt><dd class="text-slate-800">{{ $task->estimated_hours !== null ? rtrim(rtrim(number_format((float) $task->estimated_hours, 2), '0'), '.') : '—' }}</dd></div>
                    <div><dt class="text-slate-400">Actual hours</dt><dd class="text-slate-800">{{ $task->actual_hours !== null ? rtrim(rtrim(number_format((float) $task->actual_hours, 2), '0'), '.') : '—' }}</dd></div>
                    @if ($task->dependency)
                        <div class="sm:col-span-2"><dt class="text-slate-400">Depends on</dt><dd><a href="{{ route('tasks.show', $task->dependency) }}" class="text-indigo-600 hover:text-indigo-500">{{ $task->dependency->title }}</a></dd></div>
                    @endif
                </dl>

                @if ($task->description)
                    <div class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">{{ $task->description }}</div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="mb-3 font-semibold text-slate-900">Progress</h3>
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-slate-500">Completion</span>
                    <span class="font-semibold text-slate-800">{{ $task->progress }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-3 rounded-full bg-indigo-500" style="width: {{ $task->progress }}%"></div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 text-sm">
                <h3 class="mb-3 font-semibold text-slate-900">Details</h3>
                <p class="text-slate-500">Created by</p>
                <p class="mb-2 text-slate-800">{{ $task->creator?->name ?? '—' }}</p>
                <p class="text-slate-500">Created</p>
                <p class="text-slate-800">{{ $task->created_at?->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('tasks.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back to tasks</a>
    </div>
@endsection
