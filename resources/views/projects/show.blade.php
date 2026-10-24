@extends('layouts.app')

@section('title', $project->name.' — Best CPMS')
@section('heading', $project->name)

@section('actions')
    <a href="{{ route('tasks.create', ['project' => $project->id]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Add task</a>
    @can('update', $project)
        <a href="{{ route('projects.edit', $project) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Edit</a>
    @endcan
    @can('delete', $project)
        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project and all its tasks?');">
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
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Overview</h3>
                    @include('partials.project-status', ['status' => $project->status])
                </div>
                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-400">Project code</dt><dd class="text-slate-800">{{ $project->project_code ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">Client</dt><dd class="text-slate-800">{{ $project->client_name ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">Client contact</dt><dd class="text-slate-800">{{ $project->client_contact ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">Budget</dt><dd class="text-slate-800">{{ $project->budget !== null ? \App\Support\Money::format($project->budget) : '—' }}</dd></div>
                    <div><dt class="text-slate-400">Start date</dt><dd class="text-slate-800">{{ $project->start_date?->format('d M Y') ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">Target end</dt><dd class="text-slate-800">{{ $project->end_date?->format('d M Y') ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-400">Site address</dt><dd class="text-slate-800">{{ $project->site_address ?: '—' }}</dd></div>
                </dl>
                @if ($project->description)
                    <div class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">{{ $project->description }}</div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-3">
                    <h3 class="font-semibold text-slate-900">Tasks ({{ $project->tasks->count() }})</h3>
                    <a href="{{ route('tasks.create', ['project' => $project->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-500">Add task</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($project->tasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50">
                            <div>
                                <div class="font-medium text-slate-800">{{ $task->title }}</div>
                                <div class="text-xs text-slate-400">
                                    {{ $task->assignee?->name ?? 'Unassigned' }}
                                    @if ($task->due_date) · due {{ $task->due_date->format('d M Y') }} @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @include('partials.task-priority', ['priority' => $task->priority])
                                @include('partials.task-status', ['status' => $task->status])
                            </div>
                        </a>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-slate-400">No tasks on this project yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="mb-3 font-semibold text-slate-900">Progress</h3>
                @php $pct = $project->progressPercent(); @endphp
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-slate-500">Overall</span>
                    <span class="font-semibold text-slate-800">{{ $pct }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-3 rounded-full bg-indigo-500" style="width: {{ $pct }}%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-400">Average completion across {{ $project->tasks->count() }} task(s).</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 text-sm">
                <h3 class="mb-3 font-semibold text-slate-900">Details</h3>
                <p class="text-slate-500">Created by</p>
                <p class="mb-2 text-slate-800">{{ $project->creator?->name ?? '—' }}</p>
                <p class="text-slate-500">Created</p>
                <p class="text-slate-800">{{ $project->created_at?->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('projects.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back to projects</a>
    </div>
@endsection
