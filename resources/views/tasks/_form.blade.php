{{-- Shared task form. Expects: $action, $method, $projects, $assignees, $dependencies, $statuses, $priorities, optionally $task. --}}
@php
    $task = $task ?? null;
    $defaultProject = old('project_id', $task->project_id ?? request('project'));
@endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="title" class="block text-sm font-medium text-slate-700">Title <span class="text-red-500">*</span></label>
            <input id="title" name="title" type="text" required value="{{ old('title', $task->title ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="project_id" class="block text-sm font-medium text-slate-700">Project <span class="text-red-500">*</span></label>
            <select id="project_id" name="project_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select a project</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) $defaultProject === (string) $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="assigned_to" class="block text-sm font-medium text-slate-700">Assignee</label>
            <select id="assigned_to" name="assigned_to" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Unassigned</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}" @selected((string) old('assigned_to', $task->assigned_to ?? '') === (string) $assignee->id)>{{ $assignee->name }} ({{ $assignee->role?->name }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="priority" class="block text-sm font-medium text-slate-700">Priority <span class="text-red-500">*</span></label>
            <select id="priority" name="priority" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($priorities as $priority)
                    <option value="{{ $priority }}" @selected(old('priority', $task->priority ?? 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $task->status ?? 'pending') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="start_date" class="block text-sm font-medium text-slate-700">Start date</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $task?->start_date?->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="due_date" class="block text-sm font-medium text-slate-700">Due date</label>
            <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $task?->due_date?->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="progress" class="block text-sm font-medium text-slate-700">Progress (%)</label>
            <input id="progress" name="progress" type="number" min="0" max="100" required value="{{ old('progress', $task->progress ?? 0) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="estimated_hours" class="block text-sm font-medium text-slate-700">Estimated hours</label>
            <input id="estimated_hours" name="estimated_hours" type="number" step="0.01" min="0" value="{{ old('estimated_hours', $task->estimated_hours ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="actual_hours" class="block text-sm font-medium text-slate-700">Actual hours</label>
            <input id="actual_hours" name="actual_hours" type="number" step="0.01" min="0" value="{{ old('actual_hours', $task->actual_hours ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="dependency_id" class="block text-sm font-medium text-slate-700">Depends on</label>
            <select id="dependency_id" name="dependency_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">No dependency</option>
                @foreach ($dependencies as $dep)
                    <option value="{{ $dep->id }}" @selected((string) old('dependency_id', $task->dependency_id ?? '') === (string) $dep->id)>{{ $dep->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $task->description ?? '') }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('tasks.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $task ? 'Save changes' : 'Create task' }}
        </button>
    </div>
</form>
