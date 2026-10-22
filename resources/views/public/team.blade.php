@extends('layouts.public')

@section('title', 'Our Team')
@section('meta_description', 'The people building Best CPMS — a small team from construction, product, and engineering.')

@section('content')

    @include('partials.public-hero', [
        'kicker' => 'Our Team',
        'title'  => 'A small team, close to the work',
        'lead'   => 'People from site management, product, and engineering — building the tool we wished we had.',
    ])

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:py-20 lg:px-8">
            @php
                // Illustrative team — replace with real profiles before launch.
                $team = [
                    ['name' => 'Arjun Mehta',      'role' => 'Co-founder & CEO',        'bio' => 'Fifteen years running civil projects before turning to software.', 'tone' => 'bg-indigo-600'],
                    ['name' => 'Priya Nair',       'role' => 'Co-founder & CTO',        'bio' => 'Builds the systems that keep tenant data separate and safe.',       'tone' => 'bg-amber-500'],
                    ['name' => 'Rahul Desai',      'role' => 'Head of Product',         'bio' => 'Turns messy site workflows into software that stays out of the way.', 'tone' => 'bg-emerald-600'],
                    ['name' => 'Sana Qureshi',     'role' => 'Lead Engineer',           'bio' => 'Owns the planning and reporting engine end to end.',                'tone' => 'bg-slate-700'],
                    ['name' => 'Vikram Rao',       'role' => 'Customer Success',        'bio' => 'Gets your first project loaded and your team confident fast.',       'tone' => 'bg-rose-500'],
                    ['name' => 'Ananya Iyer',      'role' => 'Design',                  'bio' => 'Keeps Best CPMS legible on a laptop and a dusty phone alike.',           'tone' => 'bg-sky-600'],
                ];
            @endphp

            <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($team as $m)
                    <div>
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full {{ $m['tone'] }} font-display text-lg font-semibold text-white">{{ \Illuminate\Support\Str::of($m['name'])->explode(' ')->map(fn($p) => $p[0])->take(2)->implode('') }}</span>
                        <h3 class="mt-4 font-display text-lg font-semibold text-slate-900">{{ $m['name'] }}</h3>
                        <p class="text-sm font-medium text-indigo-600">{{ $m['role'] }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $m['bio'] }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mt-14 text-sm text-slate-400">Team profiles are illustrative and will be updated with real bios.</p>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-4xl flex-col items-center gap-4 px-5 py-14 text-center lg:px-8">
            <h2 class="font-display text-2xl font-bold text-slate-900">Want to join us?</h2>
            <p class="max-w-xl text-slate-600">We're a small team and hiring across engineering, product, and success.</p>
            <a href="{{ route('careers') }}" class="mt-2 rounded-md bg-slate-900 px-6 py-3 font-semibold text-white transition hover:bg-slate-700">See open roles</a>
        </div>
    </section>

@endsection
