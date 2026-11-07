@extends('layouts.app')

@section('title', 'Daily report '.$report->report_date->format('d M Y').' — Best CPMS')
@section('heading', 'Daily site report')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <a href="{{ route('daily-reports.index', ['project' => $report->project_id]) }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">&larr; Daily reports</a>

    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-lg font-semibold text-slate-900">{{ $report->report_date->format('l, d M Y') }}</p>
                <p class="text-sm text-slate-500">
                    @if ($report->project)
                        <a href="{{ route('projects.show', $report->project) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $report->project->name }}</a> ·
                    @endif
                    by {{ $report->user?->name ?? 'Removed user' }} · last saved {{ $report->updated_at?->format('d M, H:i') }}
                </p>
            </div>
            <div class="rounded-lg bg-slate-50 px-4 py-2 text-right">
                <p class="text-2xl font-bold text-slate-900">{{ $report->labour_count }}</p>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">labour on site</p>
            </div>
        </div>

        @if ($report->labour_breakdown)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($report->labour_breakdown as $row)
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $row['trade'] }} · {{ $row['count'] }}</span>
                @endforeach
            </div>
        @endif

        <h2 class="mt-6 text-sm font-semibold uppercase tracking-wide text-slate-500">What happened today</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-800">{{ $report->work_done }}</p>

        @if ($report->notes)
            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <h2 class="text-sm font-semibold text-amber-800">Note</h2>
                <p class="mt-1 whitespace-pre-line text-sm text-amber-900">{{ $report->notes }}</p>
            </div>
        @endif
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 font-semibold text-slate-900">Photos <span class="font-normal text-slate-400">({{ $report->photos->count() }})</span></h2>
        @if ($report->photos->isEmpty())
            <p class="text-sm text-slate-500">No photos with this report.</p>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($report->photos as $photo)
                    <figure class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                        <a href="{{ route('daily-report-photos.file', $photo) }}" target="_blank" rel="noopener" class="block aspect-[4/3] overflow-hidden bg-slate-100">
                            <img src="{{ route('daily-report-photos.file', ['photo' => $photo, 'size' => 'thumb']) }}" alt="{{ $photo->caption ?: 'Site photo' }}" loading="lazy" class="h-full w-full object-cover">
                        </a>
                        <figcaption class="flex items-center justify-between gap-2 px-2.5 py-2 text-xs text-slate-500">
                            <span class="truncate">{{ $photo->caption ?: ($photo->taken_at ?? $photo->created_at)?->format('d M, H:i') }}</span>
                            @if ($photo->canBeDeletedBy(auth()->user()))
                                <form method="POST" action="{{ route('daily-report-photos.destroy', $photo) }}" onsubmit="return confirm('Delete this photo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-700">Delete</button>
                                </form>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
