@extends('layouts.app')

@section('title', 'Cost report — Best CPMS')
@section('heading', 'Cost report')

@section('actions')
    <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">All reports</a>
    <a href="{{ route('reports.cost', array_merge(request()->only(['project']), ['export' => 'csv'])) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Download CSV</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('reports.cost') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="project" class="block text-xs font-medium text-slate-500">Project</label>
            <select id="project" name="project" onchange="this.form.submit()"
                    class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All projects</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected($selectedProject && $selectedProject->id === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <noscript><button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Filter</button></noscript>
    </form>

    {{-- Summary cards --}}
    @php
        $variance = $budget > 0 ? $budget - $totalExpenses : null;
        $net = $received - $paid;
    @endphp
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Budget</div>
            <div class="mt-1 text-lg font-semibold text-slate-900">@include('partials.money', ['value' => $budget])</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Total expenses</div>
            <div class="mt-1 text-lg font-semibold text-rose-600">@include('partials.money', ['value' => $totalExpenses])</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Payments received</div>
            <div class="mt-1 text-lg font-semibold text-emerald-600">@include('partials.money', ['value' => $received])</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Payments paid</div>
            <div class="mt-1 text-lg font-semibold text-rose-600">@include('partials.money', ['value' => $paid])</div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Budget variance</div>
            <div class="mt-1 text-lg font-semibold {{ $variance === null ? 'text-slate-400' : ($variance < 0 ? 'text-rose-600' : 'text-emerald-600') }}">
                {{ $variance === null ? '—' : \App\Support\Money::format($variance) }}
            </div>
            <p class="mt-1 text-xs text-slate-400">Budget minus expenses.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Net cash flow</div>
            <div class="mt-1 text-lg font-semibold {{ $net < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ \App\Support\Money::format($net) }}</div>
            <p class="mt-1 text-xs text-slate-400">Received minus paid.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Expenses vs received</div>
            <div class="mt-1 text-lg font-semibold {{ ($received - $totalExpenses) < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ \App\Support\Money::format($received - $totalExpenses) }}</div>
            <p class="mt-1 text-xs text-slate-400">Cash in minus cost incurred.</p>
        </div>
    </div>

    {{-- Expenses by category --}}
    <h2 class="mb-3 mt-8 text-sm font-semibold text-slate-700">Expenses by category</h2>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Entries</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Share</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($byCategory as $row)
                    @php $share = $totalExpenses > 0 ? ((float) $row->total / $totalExpenses) * 100 : 0; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-sm font-medium text-slate-700">{{ ucfirst($row->category) }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ $row->entries }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-900">@include('partials.money', ['value' => $row->total])</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">
                            <div class="flex items-center justify-end gap-2">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ number_format($share, 1) }}%"></div>
                                </div>
                                <span>{{ number_format($share, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">No expenses recorded.</td></tr>
                @endforelse
            </tbody>
            @if ($byCategory->isNotEmpty())
                <tfoot class="bg-slate-50">
                    <tr>
                        <td class="px-5 py-3 text-sm font-semibold text-slate-700">Total</td>
                        <td class="px-5 py-3 text-right text-sm font-semibold text-slate-700">{{ $byCategory->sum('entries') }}</td>
                        <td class="px-5 py-3 text-right text-sm font-semibold text-slate-900">@include('partials.money', ['value' => $totalExpenses])</td>
                        <td class="px-5 py-3 text-right text-sm font-semibold text-slate-500">100%</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
