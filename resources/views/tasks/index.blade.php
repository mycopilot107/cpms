@extends('layouts.app')

@section('title', 'Tasks — Best CPMS')
@section('heading', 'Tasks')

@section('actions')
    @can('create', App\Models\Task::class)
        <a href="{{ route('tasks.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New task</a>
    @endcan
@endsection

@section('content')
    <form method="GET" action="{{ route('tasks.index') }}" class="mb-4 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:flex sm:flex-wrap sm:items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500">Project</label>
            <select name="project" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-auto">
                <option value="">All projects</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) $filters['project'] === (string) $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-auto">
                <option value="">Any status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600 sm:py-2">
            <input type="checkbox" name="mine" value="1" @checked($filters['mine']) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            Assigned to me
        </label>
        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
            <a href="{{ route('tasks.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
        </div>
    </form>

    {{-- Desktop / tablet: table --}}
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Task</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Assignee</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $task->title }}</a>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ $task->progress }}%"></div>
                                </div>
                                <span class="text-xs text-slate-400">{{ $task->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $task->project?->name }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                        <td class="px-5 py-3">@include('partials.task-priority', ['priority' => $task->priority])</td>
                        <td class="px-5 py-3">@include('partials.task-status', ['status' => $task->status])</td>
                        <td class="px-5 py-3 text-sm {{ $task->isOverdue() ? 'font-medium text-red-600' : 'text-slate-600' }}">
                            {{ $task->due_date?->format('d M Y') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">No tasks match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile: stacked cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($tasks as $task)
            <a href="{{ route('tasks.show', $task) }}" class="block rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="font-medium text-indigo-600">{{ $task->title }}</div>
                    @include('partials.task-status', ['status' => $task->status])
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ $task->progress }}%"></div>
                    </div>
                    <span class="text-xs text-slate-400">{{ $task->progress }}%</span>
                </div>
                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div>
                        <dt class="text-xs text-slate-400">Project</dt>
                        <dd class="text-slate-700">{{ $task->project?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Assignee</dt>
                        <dd class="text-slate-700">{{ $task->assignee?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Priority</dt>
                        <dd class="mt-0.5">@include('partials.task-priority', ['priority' => $task->priority])</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Due</dt>
                        <dd class="{{ $task->isOverdue() ? 'font-medium text-red-600' : 'text-slate-700' }}">{{ $task->due_date?->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </a>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No tasks match these filters.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $tasks->links() }}</div>
@endsection
