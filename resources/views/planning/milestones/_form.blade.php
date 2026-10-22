{{-- Shared milestone form. Expects: $action, $method, $projects, $statuses, optionally $milestone, $selectedProjectId. --}}
@php $milestone = $milestone ?? null; @endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="project_id" class="block text-sm font-medium text-slate-700">Project <span class="text-red-500">*</span></label>
            <select id="project_id" name="project_id" required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select a project…</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected((int) old('project_id', $milestone->project_id ?? ($selectedProjectId ?? 0)) === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(old('status', $milestone->status ?? 'pending') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="name" class="block text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
            <input id="name" name="name" type="text" required value="{{ old('name', $milestone->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="due_date" class="block text-sm font-medium text-slate-700">Due date</label>
            <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $milestone?->due_date?->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-slate-700">Sort order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $milestone->sort_order ?? 0) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $milestone->description ?? '') }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('milestones.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $milestone ? 'Save changes' : 'Add milestone' }}
        </button>
    </div>
</form>
