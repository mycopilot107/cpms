{{-- Shared expense form. Expects: $action, $method, $projects, $categories, optionally $expense, $selectedProjectId. --}}
@php $expense = $expense ?? null; @endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="project_id" class="block text-sm font-medium text-slate-700">Project <span class="text-red-500">*</span></label>
            <select id="project_id" name="project_id" required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select a project…</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected((int) old('project_id', $expense->project_id ?? ($selectedProjectId ?? 0)) === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="category" class="block text-sm font-medium text-slate-700">Category <span class="text-red-500">*</span></label>
            <select id="category" name="category" required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected(old('category', $expense->category ?? 'material') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-slate-700">Description <span class="text-red-500">*</span></label>
            <input id="description" name="description" type="text" required value="{{ old('description', $expense->description ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="amount" class="block text-sm font-medium text-slate-700">Amount (₹) <span class="text-red-500">*</span></label>
            <input id="amount" name="amount" type="number" step="0.01" min="0" required value="{{ old('amount', $expense->amount ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="expense_date" class="block text-sm font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
            <input id="expense_date" name="expense_date" type="date" required value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="vendor" class="block text-sm font-medium text-slate-700">Vendor</label>
            <input id="vendor" name="vendor" type="text" value="{{ old('vendor', $expense->vendor ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="reference_no" class="block text-sm font-medium text-slate-700">Reference / bill no.</label>
            <input id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $expense->reference_no ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('expenses.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $expense ? 'Save changes' : 'Record expense' }}
        </button>
    </div>
</form>
