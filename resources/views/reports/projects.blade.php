@extends('layouts.app')

@section('title', 'Project report — Best CPMS')
@section('heading', 'Project report')

@section('actions')
    <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">All reports</a>
    <a href="{{ route('reports.projects', ['export' => 'csv']) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Download CSV</a>
@endsection

@section('content')
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Schedule</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Budget</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Expenses</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Variance</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Tasks</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    @php
                        $variance = $project->budget !== null ? (float) $project->budget - (float) $project->expense_total : null;
                        $pct = (int) round((float) $project->avg_progress);
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $project->name }}</a>
                            @if ($project->project_code)<div class="text-xs text-slate-400">{{ $project->project_code }}</div>@endif
                        </td>
                        <td class="px-5 py-3">@include('partials.project-status', ['status' => $project->status])</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ optional($project->start_date)->format('d M Y') ?: '—' }} → {{ optional($project->end_date)->format('d M Y') ?: '—' }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">@include('partials.money', ['value' => $project->budget, 'muted' => true])</td>
                        <td class="px-5 py-3 text-right text-sm text-rose-600">@include('partials.money', ['value' => $project->expense_total, 'muted' => true])</td>
                        <td class="px-5 py-3 text-right text-sm font-medium {{ $variance === null ? 'text-slate-400' : ($variance < 0 ? 'text-rose-600' : 'text-emerald-600') }}">{{ $variance === null ? '—' : \App\Support\Money::format($variance) }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $project->tasks_count }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $pct }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-10 text-center text-sm text-slate-400">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($projects as $project)
            @php
                $variance = $project->budget !== null ? (float) $project->budget - (float) $project->expense_total : null;
                $pct = (int) round((float) $project->avg_progress);
            @endphp
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600">{{ $project->name }}</a>
                    @include('partials.project-status', ['status' => $project->status])
                </div>
                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div><dt class="text-xs text-slate-400">Budget</dt><dd>@include('partials.money', ['value' => $project->budget, 'muted' => true])</dd></div>
                    <div><dt class="text-xs text-slate-400">Expenses</dt><dd class="text-rose-600">@include('partials.money', ['value' => $project->expense_total, 'muted' => true])</dd></div>
                    <div><dt class="text-xs text-slate-400">Variance</dt><dd class="{{ $variance === null ? 'text-slate-400' : ($variance < 0 ? 'text-rose-600' : 'text-emerald-600') }}">{{ $variance === null ? '—' : \App\Support\Money::format($variance) }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Progress</dt><dd>{{ $pct }}% · {{ $project->tasks_count }} tasks</dd></div>
                </dl>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No projects yet.</div>
        @endforelse
    </div>
@endsection
