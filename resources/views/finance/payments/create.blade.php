@extends('layouts.app')

@section('title', 'New payment — Best CPMS')
@section('heading', 'Record payment')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('finance.payments._form', [
            'action' => route('project-payments.store'),
            'method' => 'POST',
            'projects' => $projects,
            'directions' => $directions,
            'methods' => $methods,
            'selectedProjectId' => $selectedProjectId,
        ])
    </div>
@endsection
