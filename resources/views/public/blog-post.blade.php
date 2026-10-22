@extends('layouts.public')

@section('title', $post['title'])
@section('meta_description', $post['meta_description'])

@section('content')
    <article>
        <section class="relative overflow-hidden border-b border-slate-200 bg-slate-50 blueprint-grid">
            <div class="mx-auto max-w-3xl px-5 py-16 sm:py-20 lg:px-8">
                <nav class="mb-6 text-sm text-slate-500" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="transition hover:text-slate-900">Home</a>
                    <span class="mx-2 text-slate-300">/</span>
                    <a href="{{ route('blog') }}" class="transition hover:text-slate-900">Blog</a>
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-700">{{ $post['category'] }}</span>
                </nav>
                <p class="text-sm font-medium text-amber-700">{{ $post['category'] }} · {{ $post['date'] }} · {{ $post['minutes'] }} min read</p>
                <h1 class="mt-4 font-display text-4xl font-bold leading-tight tracking-tight text-slate-900">{{ $post['title'] }}</h1>
                <p class="mt-5 text-lg leading-relaxed text-slate-600">{{ $post['excerpt'] }}</p>
            </div>
        </section>

        <div class="bg-white">
            <div class="mx-auto max-w-3xl space-y-5 px-5 py-16 text-lg leading-relaxed text-slate-700 sm:py-20 lg:px-8">
                @foreach ($post['body'] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach

                @if (! empty($post['related_route']) && \Illuminate\Support\Facades\Route::has($post['related_route']))
                    <p class="border-t border-slate-200 pt-6 text-base">
                        <a href="{{ route($post['related_route']) }}" class="font-semibold text-indigo-600 hover:text-indigo-500">{{ $post['related_label'] }}</a>
                    </p>
                @endif
            </div>
        </div>
    </article>
@endsection
