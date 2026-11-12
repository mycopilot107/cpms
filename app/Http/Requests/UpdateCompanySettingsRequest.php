<?php

namespace App\Http\Requests;

use App\Support\Money;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanySettingsRequest extends FormRequest
{
    /** Only owners and admins change the company profile. */
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPage('company');
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('phone')) {
            $this->merge(['phone' => Phone::normalize($this->input('phone'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => Phone::rules(),
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', Rule::in(array_keys(Money::countries()))],
            'currency' => ['required', 'string', Rule::in(array_keys(Money::currencies()))],
            'gst_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'company name',
            'gst_number' => 'tax ID',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.digits' => 'The phone number must be 10 digits.',
        ];
    }
}
