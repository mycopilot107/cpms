@extends('layouts.app')

@section('title', 'Edit '.$company->name.' — platform')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-0">
    <a href="{{ route('platform.companies.show', $company) }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">&larr; {{ $company->name }}</a>
    <h1 class="mb-6 font-display text-2xl font-bold text-slate-900">Edit company</h1>

    @include('platform.companies._form', [
        'company' => $company,
        'action' => route('platform.companies.update', $company),
        'method' => 'PUT',
    ])
</div>
@endsection
