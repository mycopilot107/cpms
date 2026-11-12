@extends('layouts.app')

@section('title', 'New role — Best CPMS')
@section('heading', 'New role')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @include('roles._form', [
            'action' => route('roles.store'),
            'method' => 'POST',
            'pages' => $pages,
        ])
    </div>
@endsection
