<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** A super admin editing any company from the platform console. */
class UpdatePlatformCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(Company::class, 'email')->ignore($this->route('company'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['required', 'string', Rule::in(array_keys(Money::countries()))],
            'currency' => ['required', 'string', Rule::in(array_keys(Money::currencies()))],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in([Company::STATUS_ACTIVE, Company::STATUS_SUSPENDED, Company::STATUS_INACTIVE])],
            'trial_ends_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'company name',
            'email' => 'company email',
            'gst_number' => 'tax ID',
            'trial_ends_at' => 'trial end date',
        ];
    }
}
