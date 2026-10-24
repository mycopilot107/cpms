{{-- Shared user form. Expects: $action, $method, $roles, optionally $user. --}}
@php
    $user = $user ?? null;
    $isOwner = $user?->isOwner() ?? false;
@endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
            <input id="name" name="name" type="text" required value="{{ old('name', $user->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email <span class="text-red-500">*</span></label>
            <input id="email" name="email" type="email" required value="{{ old('email', $user->email ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-slate-700">Role <span class="text-red-500">*</span></label>
            @if ($isOwner)
                <input type="text" value="Owner" disabled class="mt-1 block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500">
                <input type="hidden" name="role" value="owner">
                <p class="mt-1 text-xs text-slate-400">The company owner's role can't be changed here.</p>
            @else
                <select id="role" name="role" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($roles as $role)
                        <option value="{{ $role->slug }}" @selected(old('role', $user?->role?->slug ?? 'worker') === $role->slug)>{{ $role->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
            @if ($isOwner)
                <input type="text" value="Active" disabled class="mt-1 block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500">
                <input type="hidden" name="status" value="active">
            @else
                <select id="status" name="status" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $user->status ?? 'active') === 'inactive')>Inactive</option>
                </select>
            @endif
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">
                Password @if ($user) <span class="text-slate-400">(leave blank to keep)</span> @else <span class="text-red-500">*</span> @endif
            </label>
            <input id="password" name="password" type="password" autocomplete="new-password" {{ $user ? '' : 'required' }}
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('users.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $user ? 'Save changes' : 'Add member' }}
        </button>
    </div>
</form>
