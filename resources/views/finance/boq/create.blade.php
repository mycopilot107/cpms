@extends('layouts.app')

@section('title', 'New BOQ item — Best CPMS')
@section('heading', 'New BOQ item')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('finance.boq._form', [
            'action' => route('boq-items.store'),
            'method' => 'POST',
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
        ])
    </div>
@endsection
