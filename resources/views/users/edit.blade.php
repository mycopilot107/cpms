@extends('layouts.app')

@section('title', 'Edit member — Best CPMS')
@section('heading', 'Edit team member')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @include('users._form', [
            'user' => $user,
            'action' => route('users.update', $user),
            'method' => 'PUT',
            'roles' => $roles,
        ])
    </div>
@endsection
