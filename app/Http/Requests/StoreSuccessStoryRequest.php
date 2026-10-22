<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A signed-in user submits a success story from inside the app. It lands in
 * the `pending` queue for a site administrator to approve; nothing here is
 * published automatically.
 */
class StoreSuccessStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any signed-in, active user may submit a story.
        return (bool) $this->user()?->isActive();
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:120'],
            'role'         => ['nullable', 'string', 'max:120'],
            'organisation' => ['nullable', 'string', 'max:150'],
            'email'        => ['nullable', 'email', 'max:190'],
            'rating'       => ['nullable', 'integer', 'between:1,5'],
            'story'        => ['required', 'string', 'min:30', 'max:2000'],
            'website'      => ['prohibited'], // honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'story.min'           => 'Tell us a little more — at least a sentence or two.',
            'website.prohibited'  => 'Your submission looked automated. Please try again.',
        ];
    }
}
