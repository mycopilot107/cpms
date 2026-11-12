<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The signed-in user editing their own name, email, and phone. Role, status,
 * and company are never accepted here. Changing the email needs the current
 * password, so an unattended session can't hand the account to someone else.
 */
class UpdateAccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => Phone::normalize($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $emailChanged = $this->input('email') !== strtolower((string) $user->email);
        $needsPassword = $emailChanged && is_string($user->password);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => Phone::rules(),
            'current_password' => $needsPassword ? ['required', 'string', 'current_password:web'] : ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Enter your current password to change your email.',
            'current_password.current_password' => 'Your current password is not correct.',
            'phone.digits' => 'The phone number must be 10 digits.',
        ];
    }
}
