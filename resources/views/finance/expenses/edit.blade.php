@extends('layouts.app')

@section('title', 'Edit expense — Best CPMS')
@section('heading', 'Edit expense')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('finance.expenses._form', [
            'action' => route('expenses.update', $expense),
            'method' => 'PUT',
            'projects' => $projects,
            'categories' => $categories,
            'expense' => $expense,
        ])
    </div>
@endsection
