@extends('layouts.app')

@section('title', 'Import BOQ from Excel — Best CPMS')
@section('heading', 'Import BOQ from Excel')

@section('actions')
    <a href="{{ route('boq-items.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Back to BOQ</a>
@endsection

@php
    $inputClass = 'mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
@endphp

@section('content')
    <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-3">
            <h2 class="text-base font-semibold text-slate-900">Upload the contractor's bill</h2>
            <p class="mt-1 text-sm text-slate-500">Use the Excel file as it is — title rows, section headings, and totals are recognised. You check every line before anything is saved.</p>

            @if ($projects->isEmpty())
                <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Create a project first — every BOQ belongs to a project.
                    <a href="{{ route('projects.create') }}" class="font-semibold underline">Create project</a>
                </div>
            @else
                <form method="POST" action="{{ route('boq-items.import.store') }}" enctype="multipart/form-data" class="mt-5 space-y-5" id="boq-upload">
                    @csrf

                    <div>
                        <label for="project_id" class="block text-sm font-medium text-slate-700">Project <span class="text-red-500">*</span></label>
                        <select id="project_id" name="project_id" required class="{{ $inputClass }} @error('project_id') border-red-400 @enderror">
                            <option value="">Choose a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((int) old('project_id', $selectedProjectId) === $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="file" class="block text-sm font-medium text-slate-700">Excel or CSV file <span class="text-red-500">*</span></label>
                        <label for="file" class="mt-1 flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center hover:border-indigo-400 hover:bg-indigo-50/40 @error('file') border-red-400 @enderror">
                            <span class="text-sm font-semibold text-indigo-600">Choose a file</span>
                            <span id="file-name" class="mt-1 text-xs text-slate-500">.xlsx or .csv, up to 10 MB</span>
                        </label>
                        <input id="file" name="file" type="file" required accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="sr-only">
                        @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" id="upload-button" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
                        Upload and preview
                    </button>
                </form>
            @endif
        </div>

        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-900">What the import understands</h3>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-slate-600">
                    <li>Header names like <em>Sl. No., Description of Item, Unit, Qty, Rate, Amount</em> — on any row, not just row 1.</li>
                    <li>Section rows such as <em>A. EARTHWORK</em> become the category of the lines under them.</li>
                    <li>Sub-items <em>(a), (b)…</em> keep the item text above them.</li>
                    <li>Total and sub-total rows are skipped.</li>
                    <li>Amounts like <em>₹ 1,25,000.00/-</em>; <em>LS</em> as quantity becomes 1 lump sum.</li>
                    <li>Blank rates are fine — an unpriced BOQ imports with rate 0.</li>
                </ul>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-900">Starting from scratch?</h3>
                <p class="mt-1 text-sm text-slate-600">Download the template, fill it in Excel, and upload it.</p>
                <a href="{{ route('boq-items.import.template') }}" class="mt-3 inline-flex rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Download template (CSV)</a>
            </div>
            <p class="px-1 text-xs text-slate-400">Old .xls files: open in Excel, then File → Save As → Excel Workbook (.xlsx).</p>
        </div>
    </div>

    <script>
        (function () {
            var input = document.getElementById('file');
            var label = document.getElementById('file-name');
            var form = document.getElementById('boq-upload');
            if (input && label) {
                input.addEventListener('change', function () {
                    if (input.files.length) label.textContent = input.files[0].name;
                });
            }
            if (form) {
                form.addEventListener('submit', function () {
                    var button = document.getElementById('upload-button');
                    button.disabled = true;
                    button.textContent = 'Reading the file…';
                });
            }
        })();
    </script>
@endsection
