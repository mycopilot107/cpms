@php
    $map = [
        'planning'  => 'bg-slate-100 text-slate-700',
        'active'    => 'bg-emerald-100 text-emerald-700',
        'on_hold'   => 'bg-amber-100 text-amber-700',
        'completed' => 'bg-indigo-100 text-indigo-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    $cls = $map[$status] ?? 'bg-slate-100 text-slate-700';
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cls }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
