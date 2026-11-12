@extends('layouts.app')

@section('title', 'Import projects — Best CPMS')
@section('heading', 'Import projects')

@section('actions')
    <a href="{{ route('projects.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Back to projects</a>
@endsection

@section('content')
    <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-3">
            <h2 class="text-base font-semibold text-slate-900">Upload a project list</h2>
            <p class="mt-1 text-sm text-slate-500">One row per project. You check every row before anything is saved. Rows with a problem are skipped.</p>

            <form method="POST" action="{{ route('projects.import.store') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
                @csrf
                <div>
                    <label for="file" class="block text-sm font-medium text-slate-700">Excel or CSV file <span class="text-red-500">*</span></label>
                    <label for="file" class="mt-1 flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center hover:border-indigo-400 hover:bg-indigo-50/40 @error('file') border-red-400 @enderror">
                        <span class="text-sm font-semibold text-indigo-600">Choose a file</span>
                        <span id="file-name" class="mt-1 text-xs text-slate-500">.xlsx or .csv, up to 10 MB</span>
                    </label>
                    <input id="file" name="file" type="file" required accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="sr-only">
                    @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Upload and preview
                </button>
            </form>
        </div>

        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-900">Sample Excel file</h3>
                <p class="mt-1 text-sm text-slate-600">Download it, replace the two example projects with yours, and upload it. Dates can be 2026-04-01 or 01/04/2026. Leave Status blank to start a project as planning.</p>
                <a href="{{ route('projects.import.template') }}" class="mt-3 inline-flex rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Download sample (.xlsx)</a>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-900">Columns</h3>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-slate-600">
                    <li><span class="font-medium text-slate-800">Project name</span> is required.</li>
                    <li>Project code must be unique in your company. Leave it blank if you have none.</li>
                    <li>Status: planning, active, on hold, completed, or cancelled.</li>
                    <li>Client, contact, site address, dates, budget, and description are optional.</li>
                </ul>
            </div>
            <p class="px-1 text-xs text-slate-400">Old .xls files: open in Excel, then File → Save As → Excel Workbook (.xlsx).</p>
        </div>
    </div>

    <script>
        (function () {
            var input = document.getElementById('file');
            var name = document.getElementById('file-name');
            if (!input || !name) return;
            input.addEventListener('change', function () {
                name.textContent = input.files && input.files[0] ? input.files[0].name : '.xlsx or .csv, up to 10 MB';
            });
        })();
    </script>
@endsection
