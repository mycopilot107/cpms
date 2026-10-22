<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Task::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            // Scoped exists() rules reject any id that belongs to another tenant.
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where('company_id', $companyId),
            ],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where('company_id', $companyId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'status' => ['required', Rule::in(Task::STATUSES)],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'dependency_id' => [
                'nullable',
                Rule::exists('tasks', 'id')->where('company_id', $companyId),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $status = $this->input('status');
            $progress = (int) $this->input('progress');

            if ($status === Task::STATUS_COMPLETED && $progress !== 100) {
                $validator->errors()->add('progress', 'Completed tasks must have 100% progress.');
            }

            if ($status === Task::STATUS_CANCELLED && $progress !== 0) {
                $validator->errors()->add('progress', 'Cancelled tasks must have 0% progress.');
            }

            if ($progress === 100 && $status !== Task::STATUS_COMPLETED) {
                $validator->errors()->add('status', 'Tasks at 100% progress must be completed.');
            }
        });
    }
}
