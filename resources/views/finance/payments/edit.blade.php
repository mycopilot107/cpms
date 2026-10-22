@extends('layouts.app')

@section('title', 'Edit payment — Best CPMS')
@section('heading', 'Edit payment')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('finance.payments._form', [
            'action' => route('project-payments.update', $payment),
            'method' => 'PUT',
            'projects' => $projects,
            'directions' => $directions,
            'methods' => $methods,
            'payment' => $payment,
        ])
    </div>
@endsection
