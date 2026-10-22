@extends('layouts.app')

@section('title', 'New milestone — Best CPMS')
@section('heading', 'New milestone')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('planning.milestones._form', [
            'action' => route('milestones.store'),
            'method' => 'POST',
            'projects' => $projects,
            'statuses' => $statuses,
            'selectedProjectId' => $selectedProjectId,
        ])
    </div>
@endsection
