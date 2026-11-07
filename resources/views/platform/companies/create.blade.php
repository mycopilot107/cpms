@extends('layouts.app')

@section('title', 'Add company — platform')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-0">
    <a href="{{ route('platform.companies.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">&larr; All companies</a>
    <h1 class="mb-6 font-display text-2xl font-bold text-slate-900">Add company</h1>

    @include('platform.companies._form', [
        'action' => route('platform.companies.store'),
        'method' => 'POST',
    ])
</div>
@endsection
