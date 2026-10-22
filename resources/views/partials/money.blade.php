{{--
    Shared money formatter. Usage: @include('partials.money', ['value' => $amount])
    Renders an INR amount with the ₹ symbol and Indian digit grouping.
    Pass ['muted' => true] to render a dash for null/zero.
--}}
@php
    $value = (float) ($value ?? 0);
    $formatted = '₹' . number_format($value, 2);
@endphp<span class="{{ ($muted ?? false) && $value == 0.0 ? 'text-slate-400' : '' }}">{{ ($muted ?? false) && $value == 0.0 ? '—' : $formatted }}</span>
