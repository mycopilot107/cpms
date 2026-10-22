@extends('layouts.public')

@section('title', 'Blog')
@section('meta_description', 'Notes on construction project management, scheduling, cost control, and running better projects.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Blog',
        'title'  => 'Notes from the site office',
        'lead'   => 'Practical writing on scheduling, cost control, and running construction projects that stay on plan.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-5xl px-5 py-16 sm:py-20 lg:px-8">
            @php
                $entries = collect($posts);
                $leadSlug = $entries->keys()->first();
                $lead = $entries->first();
            @endphp

            @if ($lead)
                <a href="{{ route('blog.show', $leadSlug) }}" class="block rounded-xl border border-slate-200 p-8 transition hover:border-slate-300">
                    <p class="text-sm font-medium text-indigo-600">{{ $lead['category'] }} · {{ $lead['date'] }} · {{ $lead['minutes'] }} min read</p>
                    <h2 class="mt-3 font-display text-2xl font-bold leading-snug text-slate-900">{{ $lead['title'] }}</h2>
                    <p class="mt-3 text-slate-600">{{ $lead['excerpt'] }}</p>
                </a>
            @endif

            <div class="mt-8 divide-y divide-slate-200 border-t border-slate-200">
                @foreach ($entries->slice(1) as $slug => $post)
                    <a href="{{ route('blog.show', $slug) }}" class="block py-6 transition hover:opacity-80">
                        <p class="text-sm font-medium text-indigo-600">{{ $post['category'] }} · {{ $post['date'] }} · {{ $post['minutes'] }} min read</p>
                        <h3 class="mt-1.5 font-display text-lg font-semibold text-slate-900">{{ $post['title'] }}</h3>
                        <p class="mt-1.5 text-slate-600">{{ $post['excerpt'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

@endsection
