@extends('layouts.app')

@section('title', 'My profile — Best CPMS')
@section('heading', 'My account')

@php
    $inputClass = 'mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $hasPassword = is_string($user->password);
@endphp

@section('content')
    <div class="mx-auto max-w-xl">
        @include('account._tabs')

        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <dl class="mb-6 grid grid-cols-2 gap-4 rounded-lg bg-slate-50 p-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-400">Company</dt>
                    <dd class="font-medium text-slate-800">{{ $user->company?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Role</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $user->isSuperAdmin() ? 'Platform admin' : ($user->role?->name ?? '—') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Signs in with</dt>
                    <dd class="font-medium text-slate-800">{{ $user->provider ? ucfirst($user->provider).($hasPassword ? ' or password' : '') : 'Email and password' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Member since</dt>
                    <dd class="font-medium text-slate-800">{{ $user->created_at?->format('d M Y') }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}"
                           class="{{ $inputClass }} @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="username" value="{{ old('email', $user->email) }}"
                           data-original="{{ $user->email }}"
                           class="{{ $inputClass }} @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-400">You sign in with this email on the website and the app.</p>
                </div>

                @if ($hasPassword)
                    <div id="email-password" class="{{ $errors->has('current_password') || old('email', $user->email) !== $user->email ? '' : 'hidden' }}">
                        <label for="current_password" class="block text-sm font-medium text-slate-700">Current password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                               class="{{ $inputClass }} @error('current_password') border-red-400 @enderror">
                        @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-slate-400">Needed only when you change your email.</p>
                    </div>
                @endif

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone <span class="text-slate-400">(optional)</span></label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="{{ $inputClass }}">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Save profile</button>
            </form>

            @unless ($user->isAdmin() || $user->isSuperAdmin())
                <p class="mt-5 text-xs text-slate-400">To change your role, ask your company owner or admin.</p>
            @endunless
        </div>
    </div>

    <script>
        (function () {
            var email = document.getElementById('email');
            var box = document.getElementById('email-password');
            if (!email || !box) return;
            email.addEventListener('input', function () {
                box.classList.toggle('hidden', email.value.trim().toLowerCase() === email.getAttribute('data-original').toLowerCase());
            });
        })();
    </script>
@endsection
