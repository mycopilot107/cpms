{{-- Shared project-payment form. Expects: $action, $method, $projects, $directions, $methods, optionally $payment, $selectedProjectId. --}}
@php $payment = $payment ?? null; @endphp
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
                    <option value="{{ $p->id }}" @selected((int) old('project_id', $payment->project_id ?? ($selectedProjectId ?? 0)) === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="direction" class="block text-sm font-medium text-slate-700">Direction <span class="text-red-500">*</span></label>
            <select id="direction" name="direction" required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($directions as $d)
                    <option value="{{ $d }}" @selected(old('direction', $payment->direction ?? 'incoming') === $d)>{{ ucfirst($d) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="party_name" class="block text-sm font-medium text-slate-700">Party (client / vendor)</label>
            <input id="party_name" name="party_name" type="text" value="{{ old('party_name', $payment->party_name ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="amount" class="block text-sm font-medium text-slate-700">Amount (₹) <span class="text-red-500">*</span></label>
            <input id="amount" name="amount" type="number" step="0.01" min="0" required value="{{ old('amount', $payment->amount ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="payment_date" class="block text-sm font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
            <input id="payment_date" name="payment_date" type="date" required value="{{ old('payment_date', $payment?->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="method" class="block text-sm font-medium text-slate-700">Method <span class="text-red-500">*</span></label>
            <select id="method" name="method" required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($methods as $m)
                    <option value="{{ $m }}" @selected(old('method', $payment->method ?? 'bank_transfer') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="reference_no" class="block text-sm font-medium text-slate-700">Reference no.</label>
            <input id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $payment->reference_no ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="md:col-span-2">
            <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
            <input id="notes" name="notes" type="text" value="{{ old('notes', $payment->notes ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('project-payments.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $payment ? 'Save changes' : 'Record payment' }}
        </button>
    </div>
</form>
