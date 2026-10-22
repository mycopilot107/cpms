@extends('layouts.app')

@section('title', 'New task — Best CPMS')
@section('heading', 'New task')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('tasks._form', [
            'action' => route('tasks.store'),
            'method' => 'POST',
            'projects' => $projects,
            'assignees' => $assignees,
            'dependencies' => $dependencies,
            'statuses' => $statuses,
            'priorities' => $priorities,
        ])
    </div>
@endsection
