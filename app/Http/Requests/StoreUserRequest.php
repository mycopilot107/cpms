<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }

        if ($this->exists('phone')) {
            $this->merge(['phone' => Phone::normalize($this->input('phone'))]);
        }
    }

    /**
     * Enforce the purchased seat cap. There is no limit during the trial (or
     * with no seat-limited plan); once a paid plan is active a company can only
     * hold as many user accounts as it licensed.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $company = $this->user()->company;

            if ($company && ! $company->canAddUser()) {
                $validator->errors()->add(
                    'seats',
                    "Your plan is limited to {$company->seatLimit()} users. Upgrade your subscription to add more team members."
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => Phone::rules(),
            'role' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! Role::canBeAssignedBy($this->user(), (string) $value)) {
                    $fail('The selected role is invalid.');
                }
            }],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => ['required', 'confirmed', Password::defaults()],
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
