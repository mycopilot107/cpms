@extends('layouts.app')

@section('title', 'Finance — Best CPMS')
@section('heading', 'Finance')

@section('actions')
    <a href="{{ route('boq-items.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">BOQ</a>
    <a href="{{ route('expenses.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Expenses</a>
    <a href="{{ route('project-payments.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Payments</a>
@endsection

@section('content')
    {{-- Summary cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Budget</div>
            <div class="mt-1 text-lg font-semibold text-slate-900">@include('partials.money', ['value' => $totals['budget']])</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">BOQ value</div>
            <div class="mt-1 text-lg font-semibold text-slate-900">@include('partials.money', ['value' => $totals['boq']])</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Expenses</div>
            <div class="mt-1 text-lg font-semibold text-rose-600">@include('partials.money', ['value' => $totals['expenses']])</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Received</div>
            <div class="mt-1 text-lg font-semibold text-emerald-600">@include('partials.money', ['value' => $totals['received']])</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Paid out</div>
            <div class="mt-1 text-lg font-semibold text-amber-600">@include('partials.money', ['value' => $totals['paid']])</div>
        </div>
    </div>

    {{-- Per-project breakdown: desktop table --}}
    <div class="mt-6 hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Budget</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">BOQ</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Expenses</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Variance</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Received</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    @php
                        $variance = $project->budget !== null ? (float) $project->budget - (float) $project->expense_total : null;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $project->name }}</a>
                        </td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">@include('partials.money', ['value' => $project->budget, 'muted' => true])</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">@include('partials.money', ['value' => $project->boq_total, 'muted' => true])</td>
                        <td class="px-5 py-3 text-right text-sm text-rose-600">@include('partials.money', ['value' => $project->expense_total, 'muted' => true])</td>
                        <td class="px-5 py-3 text-right text-sm font-medium {{ $variance === null ? 'text-slate-400' : ($variance < 0 ? 'text-rose-600' : 'text-emerald-600') }}">
                            {{ $variance === null ? '—' : '₹' . number_format($variance, 2) }}
                        </td>
                        <td class="px-5 py-3 text-right text-sm text-emerald-600">@include('partials.money', ['value' => $project->received_total, 'muted' => true])</td>
                        <td class="px-5 py-3 text-right text-sm text-amber-600">@include('partials.money', ['value' => $project->paid_total, 'muted' => true])</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">No projects yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Per-project breakdown: mobile cards --}}
    <div class="mt-6 space-y-3 md:hidden">
        @forelse ($projects as $project)
            @php
                $variance = $project->budget !== null ? (float) $project->budget - (float) $project->expense_total : null;
            @endphp
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600">{{ $project->name }}</a>
                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div><dt class="text-xs text-slate-400">Budget</dt><dd>@include('partials.money', ['value' => $project->budget, 'muted' => true])</dd></div>
                    <div><dt class="text-xs text-slate-400">BOQ</dt><dd>@include('partials.money', ['value' => $project->boq_total, 'muted' => true])</dd></div>
                    <div><dt class="text-xs text-slate-400">Expenses</dt><dd class="text-rose-600">@include('partials.money', ['value' => $project->expense_total, 'muted' => true])</dd></div>
                    <div><dt class="text-xs text-slate-400">Variance</dt><dd class="font-medium {{ $variance === null ? 'text-slate-400' : ($variance < 0 ? 'text-rose-600' : 'text-emerald-600') }}">{{ $variance === null ? '—' : '₹' . number_format($variance, 2) }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Received</dt><dd class="text-emerald-600">@include('partials.money', ['value' => $project->received_total, 'muted' => true])</dd></div>
                    <div><dt class="text-xs text-slate-400">Paid</dt><dd class="text-amber-600">@include('partials.money', ['value' => $project->paid_total, 'muted' => true])</dd></div>
                </dl>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No projects yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
@endsection
