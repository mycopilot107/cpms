@extends('layouts.guest')

@section('title', 'Forgot password — Best CPMS')

@section('content')
    <h1 class="mb-2 text-lg font-semibold text-slate-900">Forgot your password?</h1>
    <p class="mb-6 text-sm text-slate-500">Enter the email address for your account and we'll send you a link to choose a new password.</p>

    @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" name="email" type="email" autocomplete="username" required autofocus
                   value="{{ old('email') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Email password reset link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Remembered it?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Back to sign in</a>
    </p>
@endsection
