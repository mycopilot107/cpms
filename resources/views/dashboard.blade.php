@extends('layouts.app')

@section('title', 'Dashboard — Best CPMS')
@section('heading', 'Dashboard')

@section('actions')
    @can('create', App\Models\Project::class)
        <a href="{{ route('projects.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New project</a>
    @endcan
@endsection

@section('content')
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Welcome back, {{ auth()->user()->name }}</h2>
                <p class="text-sm text-slate-500">{{ $company?->name }}</p>
            </div>
            @if ($subscription)
                <div class="rounded-lg bg-emerald-50 px-4 py-2 text-right">
                    <div class="text-sm font-semibold text-emerald-800">{{ $usedSeats }} / {{ $subscription->seats }} seats</div>
                    <div class="text-xs font-medium uppercase tracking-wide text-emerald-700">
                        paid until {{ $subscription->ends_at?->format('d M Y') ?? 'open' }}
                    </div>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('subscription.plans') }}" class="text-xs font-semibold text-emerald-800 underline">Manage billing</a>
                    @endif
                </div>
            @elseif ($isOnTrial)
                <div class="rounded-lg bg-amber-50 px-4 py-2 text-right">
                    <div class="text-2xl font-bold text-amber-700">{{ $trialDaysRemaining }}</div>
                    <div class="text-xs font-medium uppercase tracking-wide text-amber-600">trial days left</div>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('subscription.plans') }}" class="text-xs font-semibold text-amber-800 underline">Choose a plan</a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Projects', 'value' => $stats['projects_total'], 'sub' => $stats['projects_active'].' active', 'color' => 'text-indigo-600'],
                ['label' => 'Open tasks', 'value' => $stats['tasks_open'], 'sub' => $stats['tasks_total'].' total', 'color' => 'text-sky-600'],
                ['label' => 'Overdue', 'value' => $stats['tasks_overdue'], 'sub' => 'needs attention', 'color' => 'text-red-600'],
                ['label' => 'Team', 'value' => $stats['users_total'], 'sub' => 'members', 'color' => 'text-emerald-600'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="text-sm font-medium text-slate-500">{{ $card['label'] }}</div>
                <div class="mt-2 text-3xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
                <div class="mt-1 text-xs text-slate-400">{{ $card['sub'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h3 class="font-semibold text-slate-900">Recent projects</h3>
                <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentProjects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                        <div>
                            <div class="font-medium text-slate-800">{{ $project->name }}</div>
                            <div class="text-xs text-slate-400">{{ $project->tasks_count }} task(s){{ $project->project_code ? ' · '.$project->project_code : '' }}</div>
                        </div>
                        @include('partials.project-status', ['status' => $project->status])
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">No projects yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h3 class="font-semibold text-slate-900">My open tasks</h3>
                <a href="{{ route('tasks.index', ['mine' => 1]) }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($myTasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                        <div>
                            <div class="font-medium text-slate-800">{{ $task->title }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $task->project?->name }}
                                @if ($task->due_date)
                                    · due {{ $task->due_date->format('d M Y') }}
                                @endif
                            </div>
                        </div>
                        @include('partials.task-status', ['status' => $task->status])
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">Nothing assigned to you. Enjoy the calm.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
