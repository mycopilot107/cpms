@extends('layouts.app')

@section('title', 'New project — Best CPMS')
@section('heading', 'New project')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('projects._form', [
            'action' => route('projects.store'),
            'method' => 'POST',
            'statuses' => $statuses,
        ])
    </div>
@endsection
