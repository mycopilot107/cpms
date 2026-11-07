@extends('layouts.app')

@section('title', 'Share weekly update — '.$project->name)
@section('heading', 'Share weekly update')

@section('actions')
    <a href="{{ route('projects.show', $project) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Back to project</a>
@endsection

@php
    $inputClass = 'mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $shareText = fn ($link) => "{$project->name} — weekly site update ({$link->weekLabel()}):\n".$link->url();
@endphp

@section('content')
<div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-5">
    {{-- New link --}}
    <form method="POST" action="{{ route('projects.share.store', $project) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 lg:col-span-2">
        @csrf
        <div>
            <h2 class="text-base font-semibold text-slate-900">{{ $project->name }}</h2>
            <p class="mt-1 text-sm text-slate-500">Create a link your client opens without logging in. Costs and money are never shown.</p>
        </div>

        <label class="block">
            <span class="text-sm font-medium text-slate-700">Week</span>
            <select name="week_start" class="{{ $inputClass }}">
                @foreach ($weeks as $value => $label)
                    <option value="{{ $value }}" @selected(old('week_start') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-sm font-medium text-slate-700">Message to the client <span class="text-slate-400">(optional)</span></span>
            <textarea name="message" rows="3" maxlength="1000" class="{{ $inputClass }}" placeholder="e.g. Slab of 3rd floor is done. Next week we start brickwork.">{{ old('message') }}</textarea>
        </label>

        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-slate-700">Show on the page</legend>
            @foreach ([
                'include_photos' => ['Site photos from that week', true],
                'include_labour' => ['Labour count per day', true],
                'include_notes' => ['Site notes (problems, shortages)', false],
            ] as $name => [$label, $default])
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $default)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    {{ $label }}
                </label>
            @endforeach
        </fieldset>

        <label class="block">
            <span class="text-sm font-medium text-slate-700">Link works for</span>
            <select name="expires" class="{{ $inputClass }}">
                <option value="30">30 days</option>
                <option value="90">90 days</option>
                <option value="never">Until I turn it off</option>
            </select>
        </label>

        <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Create link</button>
    </form>

    {{-- Existing links --}}
    <div class="space-y-3 lg:col-span-3">
        <h2 class="text-base font-semibold text-slate-900">Links for this project</h2>

        @forelse ($links as $link)
            @php($active = $link->isUsable())
            <div class="rounded-xl border bg-white p-5 {{ $link->id === $newLinkId ? 'border-emerald-300 ring-2 ring-emerald-100' : 'border-slate-200' }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-900">Week {{ $link->weekLabel() }}</p>
                        <p class="text-xs text-slate-500">
                            Created {{ $link->created_at->format('d M, H:i') }} by {{ $link->creator?->name ?? '—' }}
                            · opened {{ $link->view_count }} {{ \Illuminate\Support\Str::plural('time', $link->view_count) }}@if ($link->last_viewed_at), last {{ $link->last_viewed_at->diffForHumans() }}@endif
                        </p>
                    </div>
                    @if ($active)
                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            {{ $link->expires_at ? 'Works until '.$link->expires_at->format('d M Y') : 'No expiry' }}
                        </span>
                    @else
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500">{{ $link->revoked_at ? 'Turned off' : 'Expired' }}</span>
                    @endif
                </div>

                @if ($active)
                    <div class="mt-3 flex items-center gap-2">
                        <input type="text" readonly value="{{ $link->url() }}" id="link-{{ $link->id }}"
                               class="min-w-0 flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-700" onclick="this.select()">
                        <button type="button" data-copy="link-{{ $link->id }}" class="shrink-0 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Copy</button>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ rawurlencode($shareText($link)) }}" target="_blank" rel="noopener"
                           class="rounded-md bg-[#25D366] px-3 py-2 text-sm font-semibold text-white hover:opacity-90">WhatsApp</a>
                        <a href="mailto:?subject={{ rawurlencode($project->name.' — weekly site update ('.$link->weekLabel().')') }}&body={{ rawurlencode("Hello,\n\nHere is this week's progress update for {$project->name}:\n{$link->url()}\n\nRegards,\n".auth()->user()->name) }}"
                           class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Email</a>
                        <a href="{{ $link->url() }}" target="_blank" rel="noopener" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Preview</a>
                        <form method="POST" action="{{ route('share-links.revoke', $link) }}" onsubmit="return confirm('Turn this link off? The client will no longer be able to open it.');" class="ml-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">Turn off</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500">
                No links yet. Pick a week and create one — it takes a second.
            </div>
        @endforelse
    </div>
</div>

<script>
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-copy'));
            input.select();
            (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject())
                .catch(function () { document.execCommand('copy'); })
                .finally(function () {
                    button.textContent = 'Copied';
                    setTimeout(function () { button.textContent = 'Copy'; }, 1500);
                });
        });
    });
</script>
@endsection
