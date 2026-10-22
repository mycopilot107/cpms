@extends('layouts.app')

@section('title', 'Milestones — Best CPMS')
@section('heading', 'Planning · Milestones')

@section('actions')
    <a href="{{ route('planning.calendar') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Calendar</a>
    <a href="{{ route('planning.gantt') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Gantt</a>
    @can('create', App\Models\Milestone::class)
        <a href="{{ route('milestones.create', ['project' => $selectedProject?->id]) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New milestone</a>
    @endcan
@endsection

@section('content')
    <form method="GET" action="{{ route('milestones.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
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

    {{-- Desktop table --}}
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Milestone</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($milestones as $milestone)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $milestone->name }}</div>
                            @if ($milestone->description)<div class="text-xs text-slate-400">{{ Str::limit($milestone->description, 60) }}</div>@endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $milestone->project?->name }}</td>
                        <td class="px-5 py-3 text-sm {{ $milestone->isOverdue() ? 'font-medium text-rose-600' : 'text-slate-600' }}">
                            {{ $milestone->due_date?->format('d M Y') ?: '—' }}
                            @if ($milestone->isOverdue())<span class="ml-1 text-xs">(overdue)</span>@endif
                        </td>
                        <td class="px-5 py-3">@include('partials.milestone-status', ['status' => $milestone->status])</td>
                        <td class="px-5 py-3 text-right text-sm">
                            @can('update', $milestone)<a href="{{ route('milestones.edit', $milestone) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>@endcan
                            @can('delete', $milestone)
                                <form method="POST" action="{{ route('milestones.destroy', $milestone) }}" class="ml-3 inline" onsubmit="return confirm('Delete this milestone?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">No milestones yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($milestones as $milestone)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-800">{{ $milestone->name }}</div>
                        <div class="text-xs text-slate-400">{{ $milestone->project?->name }}</div>
                    </div>
                    @include('partials.milestone-status', ['status' => $milestone->status])
                </div>
                <div class="mt-3 flex items-center justify-between text-sm">
                    <span class="{{ $milestone->isOverdue() ? 'font-medium text-rose-600' : 'text-slate-600' }}">{{ $milestone->due_date?->format('d M Y') ?: 'No due date' }}</span>
                    @can('update', $milestone)<a href="{{ route('milestones.edit', $milestone) }}" class="text-indigo-600">Edit</a>@endcan
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No milestones yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $milestones->links() }}</div>
@endsection
