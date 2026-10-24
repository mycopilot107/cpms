{{--
    Shared money formatter. Usage: @include('partials.money', ['value' => $amount])
    Renders the amount in the signed-in company's currency (see App\Support\Money).
    Pass ['muted' => true] to render a dash for null/zero.
--}}
@php
    $value = (float) ($value ?? 0);
    $formatted = \App\Support\Money::format($value);
@endphp<span class="{{ ($muted ?? false) && $value == 0.0 ? 'text-slate-400' : '' }}">{{ ($muted ?? false) && $value == 0.0 ? '—' : $formatted }}</span>
