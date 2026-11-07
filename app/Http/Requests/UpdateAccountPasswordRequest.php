<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * The signed-in user changing their own password. Accounts created through
 * Google/Facebook have no password yet, so they can set one without the
 * current-password check.
 */
class UpdateAccountPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hasPassword = is_string($this->user()?->password);

        return [
            'current_password' => $hasPassword ? ['required', 'string', 'current_password:web'] : ['nullable'],
            'password' => ['required', 'string', 'confirmed', Password::defaults(), $hasPassword ? 'different:current_password' : 'nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Your current password is not correct.',
            'password.different' => 'Choose a new password that is different from the current one.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'current_password' => 'current password',
            'password' => 'new password',
        ];
    }
}
