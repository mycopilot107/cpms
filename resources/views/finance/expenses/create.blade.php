@extends('layouts.app')

@section('title', 'New expense — Best CPMS')
@section('heading', 'Record expense')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('finance.expenses._form', [
            'action' => route('expenses.store'),
            'method' => 'POST',
            'projects' => $projects,
            'categories' => $categories,
            'selectedProjectId' => $selectedProjectId,
        ])
    </div>
@endsection
