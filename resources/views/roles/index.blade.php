@extends('layouts.app')

@section('title', 'Roles — Best CPMS')
@section('heading', 'Roles')

@section('actions')
    <a href="{{ route('roles.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New role</a>
@endsection

@section('content')
    <p class="mb-4 text-sm text-slate-600">Built-in roles are shared by every company. Create a role here to choose which pages that role can open, then assign it from Team.</p>

    @error('role')
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
    @enderror

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Pages</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">People</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($roles as $role)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $role->name }}</div>
                            <div class="mt-0.5 max-w-md text-xs text-slate-500">{{ $role->description }}</div>
                            @if ($role->isSystem())
                                <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">Built-in</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">
                            {{ collect($role->pageKeys())->map(fn ($key) => $pages[$key] ?? $key)->implode(', ') ?: 'Dashboard only' }}
                        </td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $role->users_count }}</td>
                        <td class="px-5 py-3 text-right">
                            @unless ($role->isSystem())
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('roles.edit', $role) }}" class="text-sm text-indigo-600 hover:text-indigo-500">Edit</a>
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete {{ addslashes($role->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </div>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
