@extends('layouts.app')

@section('title', 'Task report — Best CPMS')
@section('heading', 'Task report')

@section('actions')
    <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">All reports</a>
    <a href="{{ route('reports.tasks', array_merge(request()->only(['project', 'status']), ['export' => 'csv'])) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Download CSV</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('reports.tasks') }}" class="mb-4 flex flex-wrap items-end gap-3">
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
        <div>
            <label for="status" class="block text-xs font-medium text-slate-500">Status</label>
            <select id="status" name="status" onchange="this.form.submit()"
                    class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All statuses</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected($selectedStatus === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>
        <noscript><button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Filter</button></noscript>
    </form>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Task</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Assignee</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3"><a href="{{ route('tasks.show', $task) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $task->title }}</a></td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $task->project?->name }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $task->assignee?->name ?: '—' }}</td>
                        <td class="px-5 py-3">@include('partials.milestone-status', ['status' => $task->status])</td>
                        <td class="px-5 py-3 text-sm {{ $task->isOverdue() ? 'font-medium text-rose-600' : 'text-slate-600' }}">{{ optional($task->due_date)->format('d M Y') ?: '—' }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $task->progress }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">No tasks match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($tasks as $task)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('tasks.show', $task) }}" class="font-medium text-indigo-600">{{ $task->title }}</a>
                    @include('partials.milestone-status', ['status' => $task->status])
                </div>
                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div><dt class="text-xs text-slate-400">Project</dt><dd>{{ $task->project?->name }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Assignee</dt><dd>{{ $task->assignee?->name ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Due</dt><dd class="{{ $task->isOverdue() ? 'text-rose-600' : '' }}">{{ optional($task->due_date)->format('d M Y') ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Progress</dt><dd>{{ $task->progress }}%</dd></div>
                </dl>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No tasks match.</div>
        @endforelse
    </div>
@endsection
