@extends('layouts.app')

@section('title', 'Payments — Best CPMS')
@section('heading', 'Project payments')

@section('actions')
    <a href="{{ route('finance.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Back to Finance</a>
    @can('create', App\Models\ProjectPayment::class)
        <a href="{{ route('project-payments.create', ['project' => $selectedProject?->id]) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New payment</a>
    @endcan
@endsection

@section('content')
    <form method="GET" action="{{ route('project-payments.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
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
        <div>
            <label for="direction" class="block text-xs font-medium text-slate-500">Direction</label>
            <select id="direction" name="direction" onchange="this.form.submit()"
                    class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="incoming" @selected($selectedDirection === 'incoming')>Incoming</option>
                <option value="outgoing" @selected($selectedDirection === 'outgoing')>Outgoing</option>
            </select>
        </div>
        <noscript><button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Filter</button></noscript>
    </form>

    {{-- Desktop table --}}
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Party</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Method</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Direction</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $payment->payment_date?->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $payment->party_name ?: '—' }}</div>
                            @if ($payment->reference_no)<div class="text-xs text-slate-400">{{ $payment->reference_no }}</div>@endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $payment->project?->name }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                        <td class="px-5 py-3">
                            @if ($payment->isIncoming())
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Incoming</span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Outgoing</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-sm font-medium {{ $payment->isIncoming() ? 'text-emerald-600' : 'text-amber-600' }}">@include('partials.money', ['value' => $payment->amount])</td>
                        <td class="px-5 py-3 text-right text-sm">
                            @can('update', $payment)<a href="{{ route('project-payments.edit', $payment) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>@endcan
                            @can('delete', $payment)
                                <form method="POST" action="{{ route('project-payments.destroy', $payment) }}" class="ml-3 inline" onsubmit="return confirm('Delete this payment?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">No payments recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($payments as $payment)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-800">{{ $payment->party_name ?: '—' }}</div>
                        <div class="text-xs text-slate-400">{{ $payment->project?->name }} · {{ $payment->payment_date?->format('d M Y') }}</div>
                    </div>
                    <div class="text-right text-sm font-semibold {{ $payment->isIncoming() ? 'text-emerald-600' : 'text-amber-600' }}">@include('partials.money', ['value' => $payment->amount])</div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    @if ($payment->isIncoming())
                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Incoming</span>
                    @else
                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Outgoing</span>
                    @endif
                    @can('update', $payment)<a href="{{ route('project-payments.edit', $payment) }}" class="text-sm text-indigo-600">Edit</a>@endcan
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No payments recorded.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
