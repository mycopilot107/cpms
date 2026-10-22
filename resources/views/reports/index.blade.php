@extends('layouts.app')

@section('title', 'Reports — Best CPMS')
@section('heading', 'Reports')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <a href="{{ route('reports.projects') }}" class="group rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-300 hover:shadow-sm">
            <div class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600">Project report</div>
            <p class="mt-1 text-sm text-slate-500">Status, schedule, budget vs spend and progress for every project.</p>
        </a>
        <a href="{{ route('reports.tasks') }}" class="group rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-300 hover:shadow-sm">
            <div class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600">Task report</div>
            <p class="mt-1 text-sm text-slate-500">All tasks with assignee, priority, status, dates and hours — filterable.</p>
        </a>
        <a href="{{ route('reports.cost') }}" class="group rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-300 hover:shadow-sm">
            <div class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600">Cost report</div>
            <p class="mt-1 text-sm text-slate-500">Expenses broken down by category, with payments and budget.</p>
        </a>
        <a href="{{ route('reports.progress') }}" class="group rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-300 hover:shadow-sm">
            <div class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600">Progress report</div>
            <p class="mt-1 text-sm text-slate-500">Completion percentage and task-status counts per project.</p>
        </a>
    </div>

    <p class="mt-6 text-xs text-slate-400">Every report has a “Download CSV” button for use in Excel or Google Sheets.</p>
@endsection
