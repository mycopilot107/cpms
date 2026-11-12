@extends('layouts.app')

@section('title', 'Check BOQ import — Best CPMS')
@section('heading', 'Check the import')

@section('actions')
    <a href="{{ route('boq-items.import.create', ['project' => $project->id]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Upload a different file</a>
@endsection

@php
    $summary = $result['summary'];
    $selectClass = 'mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $statusStyle = [
        'import' => ['Import', 'bg-emerald-50 text-emerald-700'],
        'heading' => ['Heading', 'bg-indigo-50 text-indigo-700'],
        'skip' => ['Skipped', 'bg-slate-100 text-slate-500'],
        'error' => ['Error', 'bg-rose-50 text-rose-700'],
    ];
    $stateInputs = ['sheet' => $sheet, 'header_row' => $headerRow + 1, 'headings' => $headingsAsCategory ? 1 : 0];
    $qty = fn ($value) => $value === null ? '—' : rtrim(rtrim(number_format((float) $value, 3), '0'), '.');
@endphp

@section('content')
<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">
        <span class="font-semibold text-slate-900">{{ $upload['file_name'] }}</span>
        → project <span class="font-semibold text-slate-900">{{ $project->name }}</span>.
        Nothing is saved until you click <strong>Import</strong>.
    </div>

    @error('import')
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
    @enderror

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Lines to import</p>
            <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $summary['import'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total (qty × rate)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ \App\Support\Money::format($summary['total']) }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Section headings</p>
            <p class="mt-1 text-2xl font-bold text-indigo-700">{{ $summary['heading'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Lines with notes</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ $summary['warnings'] }}</p>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Errors (skipped)</p>
            <p class="mt-1 text-2xl font-bold text-rose-700">{{ $summary['error'] }}</p>
        </div>
    </div>

    {{-- Column choices --}}
    <form method="GET" action="{{ route('boq-items.import.preview', $token) }}" class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Columns</h2>
                <p class="text-sm text-slate-500">
                    @if ($headerDetected)
                        The header was found on row {{ $headerRow + 1 }}. Change any column that was guessed wrong, then update the preview.
                    @else
                        No header row was recognised. Enter the row that has the column names, then pick the columns.
                    @endif
                </p>
            </div>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Update preview</button>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
            @if (count($upload['sheets']) > 1)
                <label class="block">
                    <span class="text-xs font-medium text-slate-500">Sheet</span>
                    <select name="sheet" class="{{ $selectClass }}">
                        @foreach ($upload['sheets'] as $name)
                            <option value="{{ $name }}" @selected($sheet === $name)>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label class="block">
                <span class="text-xs font-medium text-slate-500">Header row number</span>
                <input type="number" name="header_row" min="1" max="{{ count($rows) }}" value="{{ $headerRow + 1 }}" class="{{ $selectClass }}">
            </label>
            @foreach ($fields as $field => $label)
                <label class="block">
                    <span class="text-xs font-medium text-slate-500">{{ $label }}@if ($field === 'description') <span class="text-red-500">*</span>@endif</span>
                    <select name="map[{{ $field }}]" class="{{ $selectClass }} {{ $field === 'description' && $mapping['description'] === null ? 'border-red-400' : '' }}">
                        <option value="">— not in the sheet —</option>
                        @foreach ($columns as $index => $columnLabel)
                            <option value="{{ $index }}" @selected($mapping[$field] === $index)>{{ $columnLabel }}</option>
                        @endforeach
                    </select>
                </label>
            @endforeach
        </div>

        <label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="headings" value="0">
            <input type="checkbox" name="headings" value="1" @checked($headingsAsCategory) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            Use section rows (no quantity or rate) as the category of the lines below them
        </label>

        @if ($mapping['description'] === null)
            <p class="mt-3 text-sm font-medium text-rose-600">Choose the Description column to see the lines.</p>
        @endif
    </form>

    {{-- Rows --}}
    @if ($result['lines'] !== [])
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="max-h-[32rem] overflow-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="sticky top-0 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Row</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Item</th>
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2">Description</th>
                            <th class="px-3 py-2">Unit</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Rate</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($result['lines'] as $line)
                            @php([$statusLabel, $statusClass] = $statusStyle[$line['status']])
                            <tr class="{{ $line['status'] === 'error' ? 'bg-rose-50/40' : ($line['status'] === 'skip' ? 'text-slate-400' : '') }}">
                                <td class="px-3 py-2 font-mono text-xs text-slate-400">{{ $line['row'] }}</td>
                                <td class="px-3 py-2"><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                @if ($line['status'] === 'heading')
                                    <td colspan="7" class="px-3 py-2 font-semibold text-indigo-700">{{ $line['data']['category'] }}</td>
                                @else
                                    <td class="px-3 py-2 text-slate-600">{{ $line['data']['item_code'] ?? '' }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ $line['data']['category'] ?? '' }}</td>
                                    <td class="max-w-md px-3 py-2">
                                        <p class="line-clamp-2 whitespace-pre-line {{ $line['status'] === 'import' ? 'text-slate-800' : '' }}" title="{{ $line['data']['description'] ?? '' }}">{{ $line['data']['description'] ?? '' }}</p>
                                        @foreach ($line['messages'] as $message)
                                            <p class="mt-0.5 text-xs {{ $line['status'] === 'error' ? 'font-medium text-rose-600' : 'text-amber-700' }}">{{ $message }}</p>
                                        @endforeach
                                    </td>
                                    <td class="px-3 py-2 text-slate-600">{{ $line['data']['unit'] ?? '' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-right text-slate-700">{{ array_key_exists('quantity', $line['data']) ? $qty($line['data']['quantity']) : '' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-right text-slate-700">{{ isset($line['data']['rate']) ? \App\Support\Money::format($line['data']['rate']) : '' }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-right font-medium text-slate-900">{{ isset($line['data']['amount']) ? \App\Support\Money::format($line['data']['amount']) : '' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Confirm --}}
        <form method="POST" action="{{ route('boq-items.import.confirm', $token) }}" class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5" id="confirm-form">
            @csrf
            @foreach ($stateInputs as $name => $value)
                @if ($value !== null)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
            @endforeach
            @foreach ($mapping as $field => $column)
                <input type="hidden" name="map[{{ $field }}]" value="{{ $column }}">
            @endforeach

            <div class="text-sm text-slate-600">
                <p><strong>{{ $summary['import'] }}</strong> line(s) totalling <strong>{{ \App\Support\Money::format($summary['total']) }}</strong> will be added to {{ $project->name }}.</p>
                @if ($summary['error'] > 0)
                    <p class="text-rose-600">{{ $summary['error'] }} row(s) with errors will be skipped — fix them in Excel and import again if needed.</p>
                @endif
                @if ($existingCount > 0)
                    <label class="mt-2 flex items-center gap-2">
                        <input type="checkbox" name="replace_existing" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Replace the {{ $existingCount }} BOQ line(s) this project already has
                    </label>
                @endif
            </div>

            <button type="submit" id="confirm-button" @disabled($summary['import'] === 0)
                    class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                Import {{ $summary['import'] }} line(s)
            </button>
        </form>
    @endif
</div>

<script>
    document.getElementById('confirm-form')?.addEventListener('submit', function (event) {
        var replace = this.querySelector('input[name="replace_existing"]');
        if (replace && replace.checked && !confirm('Delete the existing BOQ lines of this project and import the new ones?')) {
            event.preventDefault();
            return;
        }
        var button = document.getElementById('confirm-button');
        button.disabled = true;
        button.textContent = 'Importing…';
    });
</script>
@endsection
