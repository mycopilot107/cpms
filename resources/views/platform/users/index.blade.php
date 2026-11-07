@extends('layouts.app')

@section('title', 'Users — platform')
@section('heading', 'All users')

@php
    $inputClass = 'rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
@endphp

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('platform.users.index') }}" class="flex flex-wrap items-end gap-3">
        <label class="block">
            <span class="text-xs font-medium text-slate-500">Search</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Name, email, phone, company…" class="{{ $inputClass }} mt-1 block w-72">
        </label>
        <label class="block">
            <span class="text-xs font-medium text-slate-500">Role</span>
            <select name="role" class="{{ $inputClass }} mt-1 block">
                <option value="all">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->slug }}" @selected($filters['role'] === $role->slug)>{{ $role->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="text-xs font-medium text-slate-500">Status</span>
            <select name="status" class="{{ $inputClass }} mt-1 block">
                <option value="all">All</option>
                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
            </select>
        </label>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
        @if ($filters['q'] !== '' || $filters['role'] !== 'all' || $filters['status'] !== 'all')
            <a href="{{ route('platform.users.index') }}" class="py-2 text-sm font-medium text-slate-500 hover:text-slate-700">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Company</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Sign-in</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $user->name }}
                                    @if ($user->isSuperAdmin())
                                        <span class="ml-1 rounded bg-indigo-100 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700">Platform admin</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500">{{ $user->email }}@if ($user->phone) · {{ $user->phone }}@endif</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->company)
                                    <a href="{{ route('platform.companies.show', $user->company) }}" class="text-slate-700 hover:text-indigo-600">{{ $user->company->name }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->role?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->provider ? ucfirst($user->provider) : 'Password' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ ucfirst($user->status) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $user->created_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">No users match.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
