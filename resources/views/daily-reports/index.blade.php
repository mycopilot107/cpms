@extends('layouts.app')

@section('title', 'Daily reports — Best CPMS')
@section('heading', 'Daily site reports')

@section('actions')
    <a href="{{ route('daily-reports.index', array_filter($filters + ['export' => 'csv'])) }}"
       class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Export CSV</a>
@endsection

@php
    $inputClass = 'mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
@endphp

@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('daily-reports.index') }}" class="flex flex-wrap items-end gap-3">
        <label class="block">
            <span class="text-xs font-medium text-slate-500">Project</span>
            <select name="project" class="{{ $inputClass }}">
                <option value="">All projects</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected($filters['project'] === $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </label>
        @if ($canViewAll)
            <label class="block">
                <span class="text-xs font-medium text-slate-500">Reported by</span>
                <select name="user" class="{{ $inputClass }}">
                    <option value="">Everyone</option>
                    @foreach ($reporters as $reporter)
                        <option value="{{ $reporter->id }}" @selected($filters['user'] === $reporter->id)>{{ $reporter->name }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        <label class="block">
            <span class="text-xs font-medium text-slate-500">From</span>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="{{ $inputClass }}">
        </label>
        <label class="block">
            <span class="text-xs font-medium text-slate-500">To</span>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="{{ $inputClass }}">
        </label>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Show</button>
    </form>

    <div class="grid grid-cols-2 gap-3 sm:max-w-md">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reports</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totals['reports'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Labour-days</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totals['labour_days']) }}</p>
        </div>
    </div>

    @if (! $canViewAll)
        <p class="text-sm text-slate-500">These are the reports you filed from the Android app.</p>
    @endif

    <div class="space-y-3">
        @forelse ($reports as $report)
            <a href="{{ route('daily-reports.show', $report) }}" class="block rounded-xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $report->report_date->format('l, d M Y') }}</p>
                        <p class="text-xs text-slate-500">{{ $report->project?->name }} · {{ $report->user?->name ?? 'Removed user' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-slate-900">{{ $report->labour_count }} <span class="text-xs font-medium text-slate-500">labour</span></p>
                        @if ($report->labour_breakdown)
                            <p class="text-xs text-slate-500">{{ collect($report->labour_breakdown)->map(fn ($row) => $row['trade'].' '.$row['count'])->implode(' · ') }}</p>
                        @endif
                    </div>
                </div>
                <p class="mt-3 line-clamp-2 whitespace-pre-line text-sm text-slate-700">{{ $report->work_done }}</p>
                @if ($report->notes)
                    <p class="mt-2 line-clamp-1 text-sm text-amber-700"><span class="font-semibold">Note:</span> {{ $report->notes }}</p>
                @endif
                @if ($report->photos->isNotEmpty())
                    <div class="mt-3 flex gap-2">
                        @foreach ($report->photos->take(5) as $photo)
                            <img src="{{ route('daily-report-photos.file', ['photo' => $photo, 'size' => 'thumb']) }}" alt="" loading="lazy" class="h-14 w-14 rounded-md object-cover">
                        @endforeach
                        @if ($report->photos->count() > 5)
                            <span class="flex h-14 w-14 items-center justify-center rounded-md bg-slate-100 text-xs font-semibold text-slate-600">+{{ $report->photos->count() - 5 }}</span>
                        @endif
                    </div>
                @endif
            </a>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <p class="font-medium text-slate-700">No reports for these dates.</p>
                <p class="mt-1 text-sm text-slate-500">Site staff file the daily report from the <strong>Daily report</strong> tab of the Android app.</p>
            </div>
        @endforelse
    </div>

    @if ($reports->hasPages())
        <div>{{ $reports->links() }}</div>
    @endif
</div>
@endsection
