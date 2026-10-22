<?php

namespace App\Http\Requests;

use App\Models\ProjectPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('projectPayment');

        return $payment
            ? $this->user()->can('update', $payment)
            : $this->user()->can('create', ProjectPayment::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where('company_id', $companyId),
            ],
            'direction' => ['required', Rule::in(ProjectPayment::DIRECTIONS)],
            'party_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', Rule::in(ProjectPayment::METHODS)],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
