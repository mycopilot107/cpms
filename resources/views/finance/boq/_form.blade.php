{{-- Shared BOQ form. Expects: $action, $method, $projects, optionally $item, $selectedProjectId. --}}
@php $item = $item ?? null; @endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="project_id" class="block text-sm font-medium text-slate-700">Project <span class="text-red-500">*</span></label>
            <select id="project_id" name="project_id" required
                    class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select a project…</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected((int) old('project_id', $item->project_id ?? ($selectedProjectId ?? 0)) === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-slate-700">Description <span class="text-red-500">*</span></label>
            <input id="description" name="description" type="text" required value="{{ old('description', $item->description ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="category" class="block text-sm font-medium text-slate-700">Category</label>
            <input id="category" name="category" type="text" list="boq-categories" value="{{ old('category', $item->category ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <datalist id="boq-categories">
                <option value="Foundation"><option value="RCC"><option value="Masonry"><option value="Finishing"><option value="Electrical"><option value="Plumbing"><option value="External">
            </datalist>
        </div>

        <div>
            <label for="item_code" class="block text-sm font-medium text-slate-700">Item code</label>
            <input id="item_code" name="item_code" type="text" value="{{ old('item_code', $item->item_code ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="quantity" class="block text-sm font-medium text-slate-700">Quantity <span class="text-red-500">*</span></label>
            <input id="quantity" name="quantity" type="number" step="0.001" min="0" required value="{{ old('quantity', $item->quantity ?? '') }}"
                   class="boq-calc mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="unit" class="block text-sm font-medium text-slate-700">Unit</label>
            <input id="unit" name="unit" type="text" list="boq-units" value="{{ old('unit', $item->unit ?? '') }}"
                   class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <datalist id="boq-units">
                <option value="Cum"><option value="Sqm"><option value="Rmt"><option value="Nos"><option value="Kg"><option value="Quintal"><option value="LS">
            </datalist>
        </div>

        <div>
            <label for="rate" class="block text-sm font-medium text-slate-700">Rate ({{ \App\Support\Money::code() }}) <span class="text-red-500">*</span></label>
            <input id="rate" name="rate" type="number" step="0.01" min="0" required value="{{ old('rate', $item->rate ?? '') }}"
                   class="boq-calc mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Amount</label>
            <div id="boq-amount" class="mt-1 block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ \App\Support\Money::format(0) }}</div>
            <p class="mt-1 text-xs text-slate-400">Quantity × rate, computed on save.</p>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('boq-items.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $item ? 'Save changes' : 'Add item' }}
        </button>
    </div>
</form>

<script>
    (function () {
        var q = document.getElementById('quantity');
        var r = document.getElementById('rate');
        var out = document.getElementById('boq-amount');
        var symbol = @json(\App\Support\Money::symbol());
        var locale = @json(\App\Support\Money::locale());
        if (!q || !r || !out) return;
        function recalc() {
            var amt = (parseFloat(q.value) || 0) * (parseFloat(r.value) || 0);
            var text;
            try { text = amt.toLocaleString(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
            catch (e) { text = amt.toFixed(2); }
            out.textContent = symbol + text;
        }
        q.addEventListener('input', recalc);
        r.addEventListener('input', recalc);
        recalc();
    })();
</script>
