@extends('layouts.app')

@section('title', 'New project — Best CPMS')
@section('heading', 'New project')

@section('actions')
    @can('create', App\Models\Project::class)
        <a href="{{ route('projects.import.create') }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Import from Excel</a>
    @endcan
@endsection

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('projects._form', [
            'action' => route('projects.store'),
            'method' => 'POST',
            'statuses' => $statuses,
        ])
    </div>
@endsection
