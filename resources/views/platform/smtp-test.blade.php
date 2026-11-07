@extends('layouts.app')

@section('title', 'SMTP test — platform')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-0">
    <header class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900">SMTP email test</h1>
        <p class="mt-1 text-sm text-slate-600">Check the mail settings Best CPMS uses for contact forms, password resets, and outreach.</p>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Current settings</h2>
            <dl class="divide-y divide-slate-100">
                @foreach ($settings as $key => $value)
                    <div class="flex items-start justify-between gap-4 py-2">
                        <dt class="shrink-0 font-mono text-xs font-semibold text-slate-500">{{ $key }}</dt>
                        <dd class="break-all text-right font-mono text-xs {{ $value ? 'text-slate-800' : 'text-rose-600' }}">{{ $value ?: '(empty)' }}</dd>
                    </div>
                @endforeach
            </dl>
            <p class="mt-4 text-xs text-slate-500">
                Change these in <code class="rounded bg-slate-100 px-1">.env</code>, then run
                <code class="rounded bg-slate-100 px-1">php artisan config:clear</code>.
            </p>
        </section>

        <div class="space-y-4">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Send a test email</h2>
                <form method="POST" action="{{ route('platform.smtp-test.send') }}" id="smtp-form">
                    @csrf
                    <label for="to_email" class="block text-sm font-medium text-slate-700">Send to</label>
                    <input type="email" name="to_email" id="to_email" required value="{{ old('to_email', $defaultTo) }}"
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('to_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <button type="submit" id="smtp-button"
                            class="mt-4 w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
                        Send test email
                    </button>
                </form>
            </section>

            @if (session('smtp_result'))
                @php($result = session('smtp_result'))
                <div class="rounded-xl border p-5 {{ $result['success'] ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
                    <p class="text-sm font-semibold {{ $result['success'] ? 'text-emerald-800' : 'text-red-800' }}">{{ $result['message'] }}</p>
                    <p class="mt-1 break-words text-xs {{ $result['success'] ? 'text-emerald-700' : 'font-mono text-red-700' }}">{{ $result['detail'] }}</p>
                </div>
            @endif

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                <p class="mb-2 text-xs font-semibold text-amber-800">Common failure reasons</p>
                <ul class="list-inside list-disc space-y-1 text-xs text-amber-700">
                    <li><strong>535 authentication failed</strong> — wrong username or password. For Gmail, use an App Password (needs 2-step verification).</li>
                    <li><strong>GoDaddy (secureserver.net)</strong> — use port 465 with SSL, and the full mailbox address as the username.</li>
                    <li><strong>Office 365 535 5.7.3</strong> — turn on "Authenticated SMTP" for the mailbox in the Microsoft 365 admin center.</li>
                    <li><strong>Connection timed out</strong> — the host or firewall blocks the port. Try 465 (SSL) or 587 (STARTTLS).</li>
                    <li><strong>Sent but not received</strong> — check spam, and make sure the from address belongs to the SMTP account's domain.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('smtp-form')?.addEventListener('submit', function () {
        var button = document.getElementById('smtp-button');
        button.disabled = true;
        button.textContent = 'Sending…';
    });
</script>
@endsection
