{{-- Payment status pill. Expects: $status ("paid", "created", "failed"). --}}
@php
    $styles = [
        \App\Models\Payment::STATUS_PAID => ['Paid', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        \App\Models\Payment::STATUS_CREATED => ['Not completed', 'bg-amber-50 text-amber-700 ring-amber-200'],
        \App\Models\Payment::STATUS_FAILED => ['Failed', 'bg-rose-50 text-rose-700 ring-rose-200'],
    ];
    [$label, $class] = $styles[$status] ?? [ucfirst((string) $status), 'bg-slate-100 text-slate-600 ring-slate-200'];
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $class }}">{{ $label }}</span>
