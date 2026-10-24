@extends('layouts.app')

@section('title', 'Company settings — Best CPMS')
@section('heading', 'Company settings')

@php
    $inputClass = 'mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
@endphp

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('company.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Company name <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" required value="{{ old('name', $company->name) }}" class="{{ $inputClass }} @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            @include('partials.country-currency-fields', [
                'country' => $company->country,
                'currency' => $company->currency,
            ])
            <p class="-mt-3 text-xs text-slate-500">The currency sets the symbol on budgets, BOQ, expenses, payments, and reports. Changing it later does not convert amounts already entered.</p>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $company->phone) }}" class="{{ $inputClass }}">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="gst_number" class="block text-sm font-medium text-slate-700">Tax ID <span class="text-slate-400">(GST, VAT, TRN, EIN…)</span></label>
                    <input id="gst_number" name="gst_number" type="text" value="{{ old('gst_number', $company->gst_number) }}" class="{{ $inputClass }}">
                    @error('gst_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
                <textarea id="address" name="address" rows="2" class="{{ $inputClass }}">{{ old('address', $company->address) }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="city" class="block text-sm font-medium text-slate-700">City</label>
                    <input id="city" name="city" type="text" value="{{ old('city', $company->city) }}" class="{{ $inputClass }}">
                    @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="state" class="block text-sm font-medium text-slate-700">State / region</label>
                    <input id="state" name="state" type="text" value="{{ old('state', $company->state) }}" class="{{ $inputClass }}">
                    @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Save settings</button>
            </div>
        </form>
    </div>
@endsection
