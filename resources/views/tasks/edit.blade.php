@extends('layouts.app')

@section('title', 'Edit task — Best CPMS')
@section('heading', 'Edit task')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('tasks._form', [
            'task' => $task,
            'action' => route('tasks.update', $task),
            'method' => 'PUT',
            'projects' => $projects,
            'assignees' => $assignees,
            'dependencies' => $dependencies,
            'statuses' => $statuses,
            'priorities' => $priorities,
        ])
    </div>
@endsection
