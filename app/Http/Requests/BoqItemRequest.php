<?php

namespace App\Http\Requests;

use App\Models\BoqItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoqItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('boqItem');

        return $item
            ? $this->user()->can('update', $item)
            : $this->user()->can('create', BoqItem::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            // project_id is validated to belong to THIS company, so a crafted
            // id from another tenant fails before it can be saved.
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where('company_id', $companyId),
            ],
            'item_code' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'unit' => ['nullable', 'string', 'max:30'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'rate' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
