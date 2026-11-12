<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use App\Support\Phone;
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

        if ($this->exists('phone')) {
            $this->merge(['phone' => Phone::normalize($this->input('phone'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        $statuses = $target->isOwner() ? ['active'] : ['active', 'inactive'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($target->id),
            ],
            'phone' => Phone::rules(),
            'role' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) use ($target): void {
                if ($target->isOwner()) {
                    if ((string) $value !== User::ROLE_OWNER) {
                        $fail('The company owner\'s role can\'t be changed.');
                    }

                    return;
                }

                if (! Role::canBeAssignedBy($this->user(), (string) $value)) {
                    $fail('The selected role is invalid.');
                }
            }],
            'status' => ['required', Rule::in($statuses)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
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
