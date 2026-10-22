@extends('layouts.app')

@section('title', 'Progress report — Best CPMS')
@section('heading', 'Progress report')

@section('actions')
    <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">All reports</a>
    <a href="{{ route('reports.progress', ['export' => 'csv']) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Download CSV</a>
@endsection

@section('content')
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Completion</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Tasks</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Completed</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">In progress</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Pending</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Overdue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    @php $pct = (int) round((float) $project->avg_progress); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $project->name }}</a>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-sm text-slate-600">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $project->tasks_count }}</td>
                        <td class="px-5 py-3 text-right text-sm text-emerald-600">{{ $project->completed_tasks_count }}</td>
                        <td class="px-5 py-3 text-right text-sm text-blue-600">{{ $project->in_progress_tasks_count }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $project->pending_tasks_count }}</td>
                        <td class="px-5 py-3 text-right text-sm {{ $project->overdue_tasks_count > 0 ? 'font-medium text-rose-600' : 'text-slate-400' }}">{{ $project->overdue_tasks_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($projects as $project)
            @php $pct = (int) round((float) $project->avg_progress); @endphp
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600">{{ $project->name }}</a>
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-sm text-slate-600">{{ $pct }}%</span>
                </div>
                <dl class="mt-3 grid grid-cols-4 gap-2 text-center text-sm">
                    <div><dt class="text-xs text-slate-400">Done</dt><dd class="text-emerald-600">{{ $project->completed_tasks_count }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Active</dt><dd class="text-blue-600">{{ $project->in_progress_tasks_count }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Pending</dt><dd class="text-slate-600">{{ $project->pending_tasks_count }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Overdue</dt><dd class="{{ $project->overdue_tasks_count > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $project->overdue_tasks_count }}</dd></div>
                </dl>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No projects yet.</div>
        @endforelse
    </div>
@endsection
