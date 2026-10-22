@extends('layouts.app')

@section('title', 'Projects — Best CPMS')
@section('heading', 'Projects')

@section('actions')
    @can('create', App\Models\Project::class)
        <a href="{{ route('projects.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New project</a>
    @endcan
@endsection

@section('content')
    {{-- Desktop / tablet: table --}}
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Progress</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Tasks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $project->name }}</a>
                            @if ($project->project_code)
                                <div class="text-xs text-slate-400">{{ $project->project_code }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $project->client_name ?: '—' }}</td>
                        <td class="px-5 py-3">@include('partials.project-status', ['status' => $project->status])</td>
                        <td class="px-5 py-3">
                            @php $pct = $project->progressPercent(); @endphp
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $project->tasks_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                            No projects yet.
                            @can('create', App\Models\Project::class)
                                <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:text-indigo-500">Create the first one</a>.
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile: stacked cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="block rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium text-indigo-600">{{ $project->name }}</div>
                        @if ($project->project_code)
                            <div class="text-xs text-slate-400">{{ $project->project_code }}</div>
                        @endif
                    </div>
                    @include('partials.project-status', ['status' => $project->status])
                </div>
                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div>
                        <dt class="text-xs text-slate-400">Client</dt>
                        <dd class="text-slate-700">{{ $project->client_name ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Tasks</dt>
                        <dd class="text-slate-700">{{ $project->tasks_count }}</dd>
                    </div>
                </dl>
                @php $pct = $project->progressPercent(); @endphp
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-xs text-slate-500">{{ $pct }}%</span>
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">
                No projects yet.
                @can('create', App\Models\Project::class)
                    <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:text-indigo-500">Create the first one</a>.
                @endcan
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
@endsection
