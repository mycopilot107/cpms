{{--
    Display-currency switcher (INR / USD).

    A small segmented control that links to `currency.switch`, which stores the
    choice in a cookie and returns to the current page. Works without
    JavaScript (plain links); the active currency is highlighted.

    Variables `$currency` (active) and `$currencies` (all) are shared with every
    view by App\Http\Middleware\ShareDisplayCurrency, with a safe fallback here.

    Optional:  'size' => 'sm'  (default) | 'md'
--}}
@php
    $all = $currencies ?? \App\Support\Currency::all();
    $active = $currency ?? \App\Support\Currency::active();
    $activeCode = $active['code'] ?? \App\Support\Currency::default();
    $pad = ($size ?? 'sm') === 'md' ? 'px-3 py-1.5 text-sm' : 'px-2.5 py-1 text-xs';
@endphp

@if (count($all) > 1)
    <div class="inline-flex items-center rounded-md border border-slate-200 bg-white p-0.5 font-semibold"
         role="group" aria-label="Display currency">
        @foreach ($all as $code => $cur)
            <a href="{{ route('currency.switch', $code) }}"
               rel="nofollow"
               title="Show prices in {{ $cur['label'] ?? $code }}"
               aria-label="Show prices in {{ $cur['label'] ?? $code }}"
               @if ($code === $activeCode) aria-current="true" @endif
               class="rounded {{ $pad }} transition {{ $code === $activeCode ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                {{ $cur['symbol'] ?? '' }} {{ $code }}
            </a>
        @endforeach
    </div>
@endif
