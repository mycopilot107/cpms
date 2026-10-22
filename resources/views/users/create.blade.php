@extends('layouts.app')

@section('title', 'Add member — Best CPMS')
@section('heading', 'Add team member')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @include('users._form', [
            'action' => route('users.store'),
            'method' => 'POST',
            'roles' => $roles,
        ])
    </div>
@endsection
