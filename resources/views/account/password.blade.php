@extends('layouts.app')

@section('title', 'Change password — Best CPMS')
@section('heading', 'My account')

@php
    $inputClass = 'mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
@endphp

@section('content')
    <div class="mx-auto max-w-xl">
    @include('account._tabs')
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="mb-1 text-base font-semibold text-slate-900">{{ $hasPassword ? 'Change password' : 'Set a password' }}</h2>
        <p class="mb-5 text-sm text-slate-500">
            @if ($hasPassword)
                Enter your current password, then choose a new one for <span class="font-medium text-slate-700">{{ auth()->user()->email }}</span>.
            @else
                You signed up with Google or Facebook. Set a password to also sign in with your email, including on the mobile app.
            @endif
        </p>

        <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            @if ($hasPassword)
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700">Current password</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                           class="{{ $inputClass }} @error('current_password') border-red-400 @enderror">
                    @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">New password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       class="{{ $inputClass }} @error('password') border-red-400 @enderror">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-400">At least 8 characters.</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="{{ $inputClass }}">
            </div>

            <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ $hasPassword ? 'Change password' : 'Set password' }}
            </button>
        </form>

        <p class="mt-5 text-xs text-slate-400">After the change you stay signed in here. The mobile app, and any browser that remembered you, will ask you to sign in again.</p>
    </div>
    </div>
@endsection
