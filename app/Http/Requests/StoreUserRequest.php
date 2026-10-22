<?php

namespace App\Http\Requests;

use App\Models\User;
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
        // New users can be given any role except owner (there is exactly one
        // owner, created at registration).
        $assignableRoles = array_values(array_diff(User::ROLES, [User::ROLE_OWNER]));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in($assignableRoles)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
