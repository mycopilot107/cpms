@extends('layouts.app')

@section('title', 'BOQ — Best CPMS')
@section('heading', 'Bill of Quantities')

@section('actions')
    <a href="{{ route('finance.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Back to Finance</a>
    @can('create', App\Models\BoqItem::class)
        <a href="{{ route('boq-items.create', ['project' => $selectedProject?->id]) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New BOQ item</a>
    @endcan
@endsection

@section('content')
    {{-- Project filter --}}
    <form method="GET" action="{{ route('boq-items.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
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

    {{-- Desktop table --}}
    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Item</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Unit</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Rate</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $item->description }}</div>
                            <div class="text-xs text-slate-400">{{ $item->category ?: '—' }}@if ($item->item_code) · {{ $item->item_code }}@endif</div>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $item->project?->name }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $item->unit ?: '—' }}</td>
                        <td class="px-5 py-3 text-right text-sm text-slate-600">@include('partials.money', ['value' => $item->rate])</td>
                        <td class="px-5 py-3 text-right text-sm font-medium text-slate-800">@include('partials.money', ['value' => $item->amount])</td>
                        <td class="px-5 py-3 text-right text-sm">
                            @can('update', $item)
                                <a href="{{ route('boq-items.edit', $item) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                            @endcan
                            @can('delete', $item)
                                <form method="POST" action="{{ route('boq-items.destroy', $item) }}" class="ml-3 inline" onsubmit="return confirm('Delete this BOQ item?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">No BOQ items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($items as $item)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-800">{{ $item->description }}</div>
                        <div class="text-xs text-slate-400">{{ $item->project?->name }}</div>
                    </div>
                    <div class="text-right text-sm font-semibold text-slate-800">@include('partials.money', ['value' => $item->amount])</div>
                </div>
                <dl class="mt-3 grid grid-cols-3 gap-2 text-sm">
                    <div><dt class="text-xs text-slate-400">Qty</dt><dd>{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }} {{ $item->unit }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Rate</dt><dd>@include('partials.money', ['value' => $item->rate])</dd></div>
                    <div class="text-right">
                        @can('update', $item)<a href="{{ route('boq-items.edit', $item) }}" class="text-indigo-600">Edit</a>@endcan
                    </div>
                </dl>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No BOQ items yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
