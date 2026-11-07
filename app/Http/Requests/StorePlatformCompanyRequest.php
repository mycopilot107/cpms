<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** A super admin adding a company and its owner from the platform console. */
class StorePlatformCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'owner_email' => strtolower(trim((string) $this->input('owner_email'))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(Company::class, 'email')],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['required', 'string', Rule::in(array_keys(Money::countries()))],
            'currency' => ['required', 'string', Rule::in(array_keys(Money::currencies()))],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'owner_password' => ['required', 'string', Password::defaults()],
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
            'owner_name' => 'owner name',
            'owner_email' => 'owner email',
            'owner_password' => 'owner password',
        ];
    }
}
