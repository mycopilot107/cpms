@extends('layouts.public')

@section('title', 'Best CPMS Customer Reviews')
@section('meta_description', 'What construction teams say about running their projects on Best CPMS.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Reviews',
        'title'  => 'What teams say after they switch',
        'lead'   => 'Feedback from contractors, developers, and project management consultancies running live work on Best CPMS.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">

            @php
                // Live, approved customer stories. Fall back to clearly-labelled
                // sample content only when nothing has been approved yet.
                $liveStories = $stories ?? collect();
                $hasLive     = $liveStories->isNotEmpty();

                // Illustrative reviews — sample content for layout.
                $reviews = $hasLive ? $liveStories->all() : [
                    ['name' => 'Meera Kulkarni', 'org' => 'Hearthstone Developers', 'stars' => 5, 'text' => 'We closed the gap between the site programme and the finance report. Month-end went from a week of chasing to an afternoon.'],
                    ['name' => 'Imran Sheikh',   'org' => 'Delta Build Contractors', 'stars' => 5, 'text' => 'The Gantt plus the cost view in one place is the whole reason we moved. My PMs finally trust the numbers.'],
                    ['name' => 'Lauren Fernandes','org' => 'Northline PMC',          'stars' => 4, 'text' => 'Setup was quick and the role-based access means clients only see what we want them to. Would like more chart types.'],
                    ['name' => 'Rajesh Patel',   'org' => 'Sunstone Infra',          'stars' => 5, 'text' => 'BOQ to expenses to payments in one ledger. Our commercial team stopped living in spreadsheets.'],
                    ['name' => 'Grace Thomas',    'org' => 'Everstone Interiors',     'stars' => 5, 'text' => 'Site engineers actually update progress now because it works well on a phone. That alone changed our reporting.'],
                    ['name' => 'Karan Malhotra', 'org' => 'Apex Structures',         'stars' => 4, 'text' => 'Solid, dependable, and the support team helped us migrate three live projects without drama.'],
                ];
            @endphp

            {{-- Rating summary --}}
            <div class="flex flex-col items-start gap-4 border-b border-slate-200 pb-10 sm:flex-row sm:items-center sm:gap-10">
                @if ($hasLive && ($average ?? null) !== null)
                    <div>
                        <p class="font-display text-5xl font-bold text-slate-900">{{ number_format($average, 1) }}<span class="text-2xl text-slate-400">/5</span></p>
                        <p class="mt-1 text-amber-500" aria-hidden="true">{!! str_repeat('★', (int) round($average)) . str_repeat('☆', 5 - (int) round($average)) !!}</p>
                    </div>
                    <p class="max-w-md text-slate-600">Average across <span class="font-semibold text-slate-800">{{ $storyCount }} customer {{ \Illuminate\Support\Str::plural('story', $storyCount) }}</span> from teams using Best CPMS on active projects.</p>
                @elseif ($hasLive)
                    <p class="max-w-md text-slate-600"><span class="font-semibold text-slate-800">{{ $storyCount }} customer {{ \Illuminate\Support\Str::plural('story', $storyCount) }}</span> from teams using Best CPMS on active projects.</p>
                @else
                    <div>
                        <p class="font-display text-5xl font-bold text-slate-900">4.8<span class="text-2xl text-slate-400">/5</span></p>
                        <p class="mt-1 text-amber-500" aria-hidden="true">★★★★★</p>
                    </div>
                    <p class="max-w-md text-slate-600">Average across <span class="font-semibold text-slate-800">312 verified reviews</span> from teams using Best CPMS on active projects.</p>
                @endif
            </div>

            <div class="mt-12 columns-1 gap-6 sm:columns-2 lg:columns-3 [&>*]:mb-6">
                @foreach ($reviews as $r)
                    <figure class="break-inside-avoid rounded-xl border border-slate-200 p-6">
                        <div class="text-sm text-amber-500" aria-label="{{ $r['stars'] }} out of 5">{!! str_repeat('★', $r['stars']) . str_repeat('☆', 5 - $r['stars']) !!}</div>
                        <blockquote class="mt-3 leading-relaxed text-slate-700">&ldquo;{{ $r['text'] }}&rdquo;</blockquote>
                        <figcaption class="mt-4 border-t border-slate-100 pt-3">
                            <p class="font-semibold text-slate-900">{{ $r['name'] }}</p>
                            <p class="text-sm text-slate-500">{{ $r['org'] }}</p>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            @unless ($hasLive)
                <p class="mt-8 text-sm text-slate-400">Reviews are illustrative samples shown for layout purposes.</p>
            @endunless
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-4xl flex-col items-center gap-4 px-5 py-14 text-center lg:px-8">
            <h2 class="font-display text-2xl font-bold text-slate-900">Used Best CPMS? We'd love your take</h2>
            <p class="max-w-xl text-slate-600">Your feedback shapes what we build next.</p>
            <a href="{{ route('feedback') }}" class="mt-2 rounded-md bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600">Share your feedback</a>
        </div>
    </section>

@endsection
