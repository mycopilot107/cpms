<?php

namespace App\Http\Requests;

use App\Support\AppPages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Create or update a company role and the pages it may open. */
class SaveRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:2000'],
            'pages' => ['nullable', 'array'],
            'pages.*' => ['string', Rule::in(AppPages::keys())],
        ];
    }

    /**
     * @return list<string>
     */
    public function pages(): array
    {
        $pages = $this->validated('pages') ?? [];

        return array_values(array_intersect(AppPages::keys(), $pages));
    }
}
