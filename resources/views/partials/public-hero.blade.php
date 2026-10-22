{{--
    Reusable public page hero. Blueprint-grid band, left-aligned.
    Usage:
      @include('partials.public-hero', [
          'kicker' => 'Pricing',                                 (optional small label)
          'title'  => 'Simple, per-seat pricing',
          'lead'   => 'One sentence of supporting context.',     (optional)
      ])
--}}
<section class="relative overflow-hidden border-b border-slate-200 bg-slate-50 blueprint-grid">
    <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
        @isset($kicker)
            <span class="kicker font-display text-sm font-medium text-amber-700">{{ $kicker }}</span>
        @endisset
        <h1 class="mt-4 max-w-3xl font-display text-4xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl">{{ $title }}</h1>
        @isset($lead)
            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">{{ $lead }}</p>
        @endisset
    </div>
</section>
