<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        // The owner's role/status are locked; everyone else is limited to the
        // non-owner roles so ownership can't be reassigned through this form.
        $roles = $target->isOwner()
            ? [User::ROLE_OWNER]
            : array_values(array_diff(User::ROLES, [User::ROLE_OWNER]));

        $statuses = $target->isOwner() ? ['active'] : ['active', 'inactive'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($target->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in($roles)],
            'status' => ['required', Rule::in($statuses)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
