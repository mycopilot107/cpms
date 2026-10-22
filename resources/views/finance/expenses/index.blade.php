@extends('layouts.app')

@section('title', 'Expenses — Best CPMS')
@section('heading', 'Expenses')

@section('actions')
    <a href="{{ route('finance.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Back to Finance</a>
    @can('create', App\Models\Expense::class)
        <a href="{{ route('expenses.create', ['project' => $selectedProject?->id]) }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">New expense</a>
    @endcan
@endsection

@section('content')
    <form method="GET" action="{{ route('expenses.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
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
            <label for="category" class="block text-xs font-medium text-slate-500">Category</label>
            <select id="category" name="category" onchange="this.form.submit()"
                    class="mt-1 block rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected($selectedCategory === $c)>{{ ucfirst($c) }}</option>
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
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Description</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $expense->expense_date?->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $expense->description }}</div>
                            @if ($expense->vendor)<div class="text-xs text-slate-400">{{ $expense->vendor }}</div>@endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $expense->project?->name }}</td>
                        <td class="px-5 py-3"><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ ucfirst($expense->category) }}</span></td>
                        <td class="px-5 py-3 text-right text-sm font-medium text-rose-600">@include('partials.money', ['value' => $expense->amount])</td>
                        <td class="px-5 py-3 text-right text-sm">
                            @can('update', $expense)<a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>@endcan
                            @can('delete', $expense)
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="ml-3 inline" onsubmit="return confirm('Delete this expense?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">No expenses recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($expenses as $expense)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-800">{{ $expense->description }}</div>
                        <div class="text-xs text-slate-400">{{ $expense->project?->name }} · {{ $expense->expense_date?->format('d M Y') }}</div>
                    </div>
                    <div class="text-right text-sm font-semibold text-rose-600">@include('partials.money', ['value' => $expense->amount])</div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ ucfirst($expense->category) }}</span>
                    @can('update', $expense)<a href="{{ route('expenses.edit', $expense) }}" class="text-sm text-indigo-600">Edit</a>@endcan
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-400">No expenses recorded.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>
@endsection
