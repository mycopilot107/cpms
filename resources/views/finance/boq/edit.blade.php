@extends('layouts.app')

@section('title', 'Edit BOQ item — Best CPMS')
@section('heading', 'Edit BOQ item')

@section('content')
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        @include('finance.boq._form', [
            'action' => route('boq-items.update', $item),
            'method' => 'PUT',
            'projects' => $projects,
            'item' => $item,
        ])
    </div>
@endsection
