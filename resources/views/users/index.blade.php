@extends('layouts.app')

@section('title', 'Team — Best CPMS')
@section('heading', 'Team')

@section('actions')
    @can('create', App\Models\User::class)
        <a href="{{ route('users.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add member</a>
    @endcan
@endsection

@section('content')
    {{-- Desktop / tablet: table --}}
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $user->name }}</div>
                            @if ($user->phone)<div class="text-xs text-slate-400">{{ $user->phone }}</div>@endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ ucfirst($user->role) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if ($user->status === 'active')
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $user)
                                    <a href="{{ route('users.edit', $user) }}" class="text-sm text-indigo-600 hover:text-indigo-500">Edit</a>
                                @endcan
                                @can('delete', $user)
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Remove {{ addslashes($user->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-500">Remove</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile: stacked cards --}}
    <div class="space-y-3 md:hidden">
        @foreach ($users as $user)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-800">{{ $user->name }}</div>
                        <div class="text-sm text-slate-500">{{ $user->email }}</div>
                        @if ($user->phone)<div class="text-xs text-slate-400">{{ $user->phone }}</div>@endif
                    </div>
                    @if ($user->status === 'active')
                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                    @endif
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ ucfirst($user->role) }}</span>
                    <div class="flex items-center gap-3">
                        @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}" class="text-sm text-indigo-600 hover:text-indigo-500">Edit</a>
                        @endcan
                        @can('delete', $user)
                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Remove {{ addslashes($user->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-500">Remove</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
