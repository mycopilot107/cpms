{{-- Shared project form. Expects: $action, $method, $statuses, and optionally $project. --}}
@php $project = $project ?? null; @endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="block text-sm font-medium text-slate-700">Project name <span class="text-red-500">*</span></label>
            <input id="name" name="name" type="text" required value="{{ old('name', $project->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="project_code" class="block text-sm font-medium text-slate-700">Project code</label>
            <input id="project_code" name="project_code" type="text" value="{{ old('project_code', $project->project_code ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <p class="mt-1 text-xs text-slate-400">Unique within your company.</p>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $project->status ?? 'planning') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="client_name" class="block text-sm font-medium text-slate-700">Client name</label>
            <input id="client_name" name="client_name" type="text" value="{{ old('client_name', $project->client_name ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="client_contact" class="block text-sm font-medium text-slate-700">Client contact</label>
            <input id="client_contact" name="client_contact" type="text" value="{{ old('client_contact', $project->client_contact ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="md:col-span-2">
            <label for="site_address" class="block text-sm font-medium text-slate-700">Site address</label>
            <input id="site_address" name="site_address" type="text" value="{{ old('site_address', $project->site_address ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="start_date" class="block text-sm font-medium text-slate-700">Start date</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $project?->start_date?->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="end_date" class="block text-sm font-medium text-slate-700">Target end date</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $project?->end_date?->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="budget" class="block text-sm font-medium text-slate-700">Budget ({{ \App\Support\Money::code() }})</label>
            <input id="budget" name="budget" type="number" step="0.01" min="0" value="{{ old('budget', $project->budget ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $project->description ?? '') }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('projects.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $project ? 'Save changes' : 'Create project' }}
        </button>
    </div>
</form>
