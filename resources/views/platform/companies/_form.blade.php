{{--
    Platform company form. Expects: $action, $method, optional $company.
    On create it also asks for the owner login and the trial length.
--}}
@php
    $company = $company ?? null;
    $isCreate = $company === null;
    $inputClass = 'mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $field = fn (string $name) => old($name, $company?->{$name});
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Company</h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Company name <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" required value="{{ $field('name') }}" class="{{ $inputClass }}">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Company email <span class="text-red-500">*</span></label>
                <input id="email" name="email" type="email" required value="{{ $field('email') }}" class="{{ $inputClass }}">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ $field('phone') }}" class="{{ $inputClass }}">
                @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="gst_number" class="block text-sm font-medium text-slate-700">Tax ID <span class="text-slate-400">(GST, VAT, TRN…)</span></label>
                <input id="gst_number" name="gst_number" type="text" value="{{ $field('gst_number') }}" class="{{ $inputClass }}">
                @error('gst_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-5">
            @include('partials.country-currency-fields', [
                'country' => $company?->country,
                'currency' => $company?->currency,
            ])
        </div>

        <div class="mt-5">
            <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
            <textarea id="address" name="address" rows="2" class="{{ $inputClass }}">{{ $field('address') }}</textarea>
            @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="city" class="block text-sm font-medium text-slate-700">City</label>
                <input id="city" name="city" type="text" value="{{ $field('city') }}" class="{{ $inputClass }}">
                @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="state" class="block text-sm font-medium text-slate-700">State / region</label>
                <input id="state" name="state" type="text" value="{{ $field('state') }}" class="{{ $inputClass }}">
                @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Access</h2>

        @if ($isCreate)
            <div class="sm:w-1/2">
                <label for="trial_days" class="block text-sm font-medium text-slate-700">Free trial (days)</label>
                <input id="trial_days" name="trial_days" type="number" min="0" max="365" value="{{ old('trial_days') }}" placeholder="Standard" class="{{ $inputClass }}">
                <p class="mt-1 text-xs text-slate-500">Leave empty for the standard trial: at least {{ \App\Models\Company::TRIAL_DAYS }} days and past the first month-end — today that is until {{ $standardTrialEnds->format('j M Y') }}.</p>
                @error('trial_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" class="{{ $inputClass }}">
                        @foreach ([\App\Models\Company::STATUS_ACTIVE => 'Active', \App\Models\Company::STATUS_SUSPENDED => 'Suspended', \App\Models\Company::STATUS_INACTIVE => 'Inactive'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $company->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Suspended or inactive blocks every user of this company on the website and the app. Data is kept.</p>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="trial_ends_at" class="block text-sm font-medium text-slate-700">Trial ends on</label>
                    <input id="trial_ends_at" name="trial_ends_at" type="date" value="{{ old('trial_ends_at', $company->trial_ends_at?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                    <p class="mt-1 text-xs text-slate-500">Move this date forward to extend the free trial.</p>
                    @error('trial_ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        @endif
    </section>

    @if ($isCreate)
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 font-display text-sm font-semibold uppercase tracking-wide text-slate-500">Owner login</h2>
            <p class="mb-4 text-sm text-slate-500">The owner signs in with this email and password, then adds the rest of the team.</p>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="owner_name" class="block text-sm font-medium text-slate-700">Owner name <span class="text-red-500">*</span></label>
                    <input id="owner_name" name="owner_name" type="text" required value="{{ old('owner_name') }}" class="{{ $inputClass }}">
                    @error('owner_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="owner_email" class="block text-sm font-medium text-slate-700">Owner email <span class="text-red-500">*</span></label>
                    <input id="owner_email" name="owner_email" type="email" required value="{{ old('owner_email') }}" class="{{ $inputClass }}">
                    @error('owner_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="owner_password" class="block text-sm font-medium text-slate-700">Password <span class="text-red-500">*</span></label>
                    <input id="owner_password" name="owner_password" type="text" required autocomplete="new-password" class="{{ $inputClass }} font-mono">
                    <p class="mt-1 text-xs text-slate-500">Share it with the owner securely. They can change it later.</p>
                    @error('owner_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>
    @endif

    <div class="flex items-center justify-end gap-3">
        <a href="{{ $isCreate ? route('platform.companies.index') : route('platform.companies.show', $company) }}"
           class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ $isCreate ? 'Add company' : 'Save changes' }}
        </button>
    </div>
</form>
