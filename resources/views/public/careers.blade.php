@extends('layouts.public')

@section('title', 'Careers')
@section('meta_description', 'Join Best CPMS. We\'re a small, focused team building software for the people who build.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Careers',
        'title'  => 'Build the tools that build things',
        'lead'   => 'We\'re a small team that stays close to our customers and ships work we\'re proud of. If that sounds like you, let\'s talk.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:py-20 lg:px-8">
            <div class="grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['Work that ships', 'Small team, short path from idea to production. Your work reaches customers in days, not quarters.'],
                    ['Close to the field', 'We spend time with real construction teams. You\'ll understand who you\'re building for.'],
                    ['Remote-friendly', 'Work where you do your best. We keep meetings few and writing clear.'],
                ] as $p)
                    <div class="border-l-2 border-amber-500 pl-4">
                        <h3 class="font-display font-semibold text-slate-900">{{ $p[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $p[1] }}</p>
                    </div>
                @endforeach
            </div>

            <h2 class="mt-16 font-display text-2xl font-bold text-slate-900">Open roles</h2>
            @php
                $roles = [
                    ['title' => 'Senior Backend Engineer', 'team' => 'Engineering', 'type' => 'Full-time', 'place' => 'Remote (IST ±3h)'],
                    ['title' => 'Product Designer',        'team' => 'Design',      'type' => 'Full-time', 'place' => 'Remote / Bengaluru'],
                    ['title' => 'Customer Success Lead',   'team' => 'Success',     'type' => 'Full-time', 'place' => 'Bengaluru'],
                    ['title' => 'Implementation Specialist','team' => 'Success',    'type' => 'Contract',  'place' => 'Remote'],
                ];
            @endphp
            <div class="mt-6 divide-y divide-slate-200 border-t border-slate-200">
                @foreach ($roles as $role)
                    <div class="flex flex-col gap-3 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-display text-lg font-semibold text-slate-900">{{ $role['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $role['team'] }} · {{ $role['type'] }} · {{ $role['place'] }}</p>
                        </div>
                        <a href="{{ route('contact') }}" class="shrink-0 rounded-md border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:border-slate-400">Apply</a>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 rounded-xl border border-slate-200 bg-slate-50 p-6">
                <p class="text-slate-700">Don't see your role? We're always glad to meet good people.</p>
                <a href="{{ route('contact') }}" class="mt-3 inline-block font-semibold text-indigo-600 hover:text-indigo-700">Send us an introduction</a>
            </div>
        </div>
    </section>

@endsection
