<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Send Email — Best CPMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
@php
    $inputClass = 'w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
    $filters = [
        'all' => ['All', 'bg-slate-800 border-slate-800'],
        'pending' => ['Pending', 'bg-amber-600 border-amber-600'],
        'sent' => ['Sent', 'bg-emerald-600 border-emerald-600'],
        'failed' => ['Failed', 'bg-red-600 border-red-600'],
        'unsubscribed' => ['Unsubscribed', 'bg-slate-600 border-slate-600'],
    ];
@endphp

<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-800">Send email to contractors &amp; builders</h1>
        <form method="POST" action="{{ route('admin.send-email.send-now') }}">
            @csrf
            <button type="submit" @disabled($counts['pending'] === 0)
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                Send next {{ $batchSize }} now ({{ $counts['pending'] }} pending)
            </button>
        </form>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    {{-- Search --}}
    <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-base font-semibold text-slate-800">Find contractor &amp; builder emails</h2>
        <p class="mb-4 text-xs text-slate-500">
            Type what to search for, such as <em>construction contractors indore</em> or <em>builders in pune</em>.
            Only contact emails published on business websites are collected, up to <strong>{{ $emailLimit }}</strong> per search.
            An address already in the list, or already a Best CPMS user, is never added or emailed again.
        </p>

        <form id="search-form" method="POST" action="{{ route('admin.send-email.search') }}">
            @csrf
            <input type="hidden" name="subject" value="{{ old('subject', $defaultSubject) }}">
            <input type="hidden" name="body" value="{{ old('body', $defaultBody) }}">

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <label for="query" class="sr-only">Search text</label>
                    <input type="text" name="query" id="query" value="{{ old('query') }}" required minlength="3" maxlength="200"
                           placeholder="construction contractors indore" class="{{ $inputClass }}">
                    @error('query')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" id="search-button"
                        class="whitespace-nowrap rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-900">
                    Search and queue emails
                </button>
            </div>
            <p id="search-wait" class="mt-2 hidden text-xs text-slate-500">Searching websites — this can take 1 to 4 minutes. Keep this tab open…</p>
        </form>

        @if (session('queued_emails'))
            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="mb-2 text-xs font-semibold text-slate-600">Queued just now</p>
                <ul class="space-y-1 font-mono text-sm text-slate-800">
                    @foreach (session('queued_emails') as $address)
                        <li>{{ $address }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Compose / paste --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-slate-800">Email content</h2>

            <form method="POST" action="{{ route('admin.send-email.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="subject" class="mb-1.5 block text-sm font-medium text-slate-700">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $defaultSubject) }}" required class="{{ $inputClass }}">
                    @error('subject')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="body" class="mb-1.5 block text-sm font-medium text-slate-700">Body (HTML allowed)</label>
                    <textarea name="body" id="body" rows="12" required class="{{ $inputClass }} font-mono">{{ old('body', $defaultBody) }}</textarea>
                    @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-400">An unsubscribe link is added to the bottom of every email automatically.</p>
                </div>

                <div class="mb-4">
                    <label for="emails" class="mb-1.5 block text-sm font-medium text-slate-700">Or paste emails yourself (comma or new line separated)</label>
                    <textarea name="emails" id="emails" rows="3" class="{{ $inputClass }}"
                              placeholder="info@builder1.com, contact@contractor2.in">{{ old('emails') }}</textarea>
                    @error('emails')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    Queue pasted emails
                </button>
            </form>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
            <p class="mb-2 text-xs font-semibold text-amber-800">How sending works</p>
            <ul class="list-inside list-disc space-y-1 text-xs text-amber-700">
                <li>Search or paste addresses — each one is saved once, as <strong>Pending</strong>.</li>
                <li>Click <strong>Send next {{ $batchSize }} now</strong>, or let the scheduler send {{ $batchSize }} every minute.</li>
                <li>Scheduler: run <code>php artisan schedule:work</code> on this computer, or add the cron line <code>* * * * * php artisan schedule:run</code> on the server.</li>
                <li>A sent, failed, or unsubscribed address is never emailed again — the same address cannot be added twice.</li>
                <li>Search text and the subject/body above are used for the next search.</li>
            </ul>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        @foreach ($filters as $key => [$label, $activeClass])
            @php($isActive = ($status ?? 'all') === $key)
            <a href="{{ route('admin.send-email.index', $key === 'all' ? [] : ['status' => $key]) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $isActive ? $activeClass.' text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50' }}">
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </div>

    {{-- List --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-slate-600">ID</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-slate-600">Email</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-slate-600">Found by</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-slate-600">Sent at</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold text-slate-600">Added</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($emails as $row)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-400">{{ $row->id }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $row->email }}</td>
                            <td class="max-w-[220px] truncate px-4 py-3 text-slate-500">{{ $row->search_query ?? 'Pasted' }}</td>
                            <td class="px-4 py-3">
                                @if ($row->unsubscribed_at)
                                    <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">Unsubscribed</span>
                                @elseif ($row->is_send)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Sent</span>
                                @elseif ($row->error)
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700" title="{{ $row->error }}">Failed</span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $row->sent_at?->format('d M Y, H:i') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $row->created_at?->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">No emails yet. Search above to find contractors and builders.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($emails->hasPages())
            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">{{ $emails->links() }}</div>
        @endif
    </div>
</div>

<script>
    document.getElementById('search-form')?.addEventListener('submit', function () {
        var subject = document.getElementById('subject');
        var body = document.getElementById('body');
        if (subject) this.querySelector('input[name="subject"]').value = subject.value;
        if (body) this.querySelector('input[name="body"]').value = body.value;
        document.getElementById('search-button').disabled = true;
        document.getElementById('search-wait').classList.remove('hidden');
    });
</script>
</body>
</html>
