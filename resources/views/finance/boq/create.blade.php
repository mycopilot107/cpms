@extends('layouts.app')

@section('title', 'New BOQ item — Best CPMS')
@section('heading', 'New BOQ item')

@section('content')
    <div class="mx-auto mb-4 flex max-w-3xl items-center justify-between gap-3 rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-3 text-sm text-indigo-800">
        <span>Already have the BOQ in Excel? Import all lines at once.</span>
        <a href="{{ route('boq-items.import.create', ['project' => $selectedProjectId]) }}" class="shrink-0 font-semibold underline">Import from Excel</a>
    </div>
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('finance.boq._form', [
            'action' => route('boq-items.store'),
            'method' => 'POST',
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
        ])
    </div>
@endsection
