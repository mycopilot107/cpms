@extends('layouts.app')

@section('title', 'Edit milestone — Best CPMS')
@section('heading', 'Edit milestone')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('planning.milestones._form', [
            'action' => route('milestones.update', $milestone),
            'method' => 'PUT',
            'projects' => $projects,
            'statuses' => $statuses,
            'milestone' => $milestone,
        ])
    </div>
@endsection
