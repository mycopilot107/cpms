@php
    $map = [
        'low'    => 'bg-slate-100 text-slate-600',
        'medium' => 'bg-sky-100 text-sky-700',
        'high'   => 'bg-amber-100 text-amber-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];
    $cls = $map[$priority] ?? 'bg-slate-100 text-slate-600';
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cls }}">{{ ucfirst($priority) }}</span>
