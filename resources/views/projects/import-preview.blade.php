@extends('layouts.app')

@section('title', 'Check project import — Best CPMS')
@section('heading', 'Check project import')

@section('actions')
    <a href="{{ route('projects.import.create') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Upload a different file</a>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            {{ $upload['file_name'] }} —
            <span class="font-medium text-slate-800">{{ $upload['summary']['import'] }} ready</span>,
            <span class="font-medium text-slate-800">{{ $upload['summary']['error'] }} with errors</span>.
            Only the ready rows are saved.
        </p>
        <form method="POST" action="{{ route('projects.import.confirm', $token) }}">
            @csrf
            <button type="submit" @disabled($upload['summary']['import'] === 0)
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                Import {{ $upload['summary']['import'] }} project(s)
            </button>
        </form>
    </div>

    @error('import')
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
    @enderror

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Row</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Result</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($upload['lines'] as $line)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ $line['row'] }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $line['label'] }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $line['data']['project_code'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $line['data']['client_name'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ isset($line['data']['status']) ? ucfirst(str_replace('_', ' ', $line['data']['status'])) : '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($line['status'] === 'import')
                                <span class="font-medium text-emerald-700">Ready</span>
                            @else
                                <span class="text-red-700">{{ implode(' ', $line['messages']) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
