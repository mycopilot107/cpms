@extends('layouts.app')

@section('title', 'Edit project — Best CPMS')
@section('heading', 'Edit project')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('projects._form', [
            'project' => $project,
            'action' => route('projects.update', $project),
            'method' => 'PUT',
            'statuses' => $statuses,
        ])
    </div>
@endsection
