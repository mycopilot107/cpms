@extends('layouts.app')

@section('title', 'Edit role — Best CPMS')
@section('heading', 'Edit '.$role->name)

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @include('roles._form', [
            'action' => route('roles.update', $role),
            'method' => 'PUT',
            'pages' => $pages,
            'role' => $role,
        ])
    </div>
@endsection
