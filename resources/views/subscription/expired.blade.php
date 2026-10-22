@extends('layouts.app')

@section('title', 'Trial ended — Best CPMS')
@section('heading', 'Subscription')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-2xl text-red-500">!</div>
        <h2 class="text-xl font-semibold text-slate-900">Your free trial has ended</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
            Thanks for trying Best CPMS. To keep managing projects and tasks for
            <span class="font-medium text-slate-700">{{ auth()->user()?->company?->name }}</span>,
            choose a plan to continue. Your data is safe and will be here when you upgrade.
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            @if (auth()->user()?->isAdmin())
                <a href="{{ route('subscription.plans') }}"
                   class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Choose a plan
                </a>
            @else
                <span class="rounded-md bg-slate-100 px-4 py-2 text-sm font-medium text-slate-500">
                    Ask your owner or admin to renew the subscription.
                </span>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Log out</button>
            </form>
        </div>
    </div>
@endsection
