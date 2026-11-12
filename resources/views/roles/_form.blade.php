{{-- Role form. Expects $action, $method, $pages, and optionally $role. --}}
@php $role = $role ?? null; @endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" required value="{{ old('name', $role->name ?? '') }}"
               class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Description <span class="text-red-500">*</span></label>
        <textarea id="description" name="description" rows="3" required
                  class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $role->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <fieldset>
        <legend class="text-sm font-medium text-slate-700">Pages this role can open</legend>
        <p class="mt-1 text-xs text-slate-500">Dashboard is included for every role. Tick the rest of the menu this role should see.</p>
        @php $selected = old('pages', $role?->pageKeys() ?? []); @endphp
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach ($pages as $key => $label)
                <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="pages[]" value="{{ $key }}" @checked(in_array($key, $selected, true))
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    {{ $label }}
                </label>
            @endforeach
        </div>
        @error('pages')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('pages.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </fieldset>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('roles.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $role ? 'Save role' : 'Create role' }}
        </button>
    </div>
</form>
