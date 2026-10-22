@php
    $map = [
        'pending'     => 'bg-slate-100 text-slate-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'completed'   => 'bg-emerald-100 text-emerald-700',
        'delayed'     => 'bg-red-100 text-red-700',
        'cancelled'   => 'bg-red-100 text-red-700',
    ];
    $cls = $map[$status] ?? 'bg-slate-100 text-slate-700';
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cls }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
