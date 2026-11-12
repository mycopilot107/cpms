@extends('layouts.guest')

@section('title', 'Create your account — Best CPMS')

@section('content')
    <h1 class="mb-1 text-lg font-semibold text-slate-900">Start your free trial</h1>
    <p class="mb-6 text-sm text-slate-500">Free until {{ \App\Models\Company::trialEndsAt(now())->format('j M') }} — at least {{ \App\Models\Company::TRIAL_DAYS }} days and through your first month-end report. No credit card required.</p>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="company_name" class="block text-sm font-medium text-slate-700">Company name</label>
            <input id="company_name" name="company_name" type="text" required autofocus
                   value="{{ old('company_name') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('company_name') border-red-400 @enderror">
            @error('company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Your name</label>
            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Work email</label>
            <input id="email" name="email" type="email" autocomplete="username" required value="{{ old('email') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700">Phone <span class="text-slate-400">(optional, 10 digits)</span></label>
            <input id="phone" name="phone" type="text" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" placeholder="10-digit number"
                   value="{{ old('phone') }}"
                   oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-400 @enderror">
            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        @include('partials.country-currency-fields', ['detect' => true])

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Create account &amp; start trial
        </button>
    </form>

    @include('auth.partials.social-buttons', ['labelVerb' => 'Sign up'])

    @include('auth.partials.app-download')

    <p class="mt-6 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Sign in</a>
    </p>
@endsection
