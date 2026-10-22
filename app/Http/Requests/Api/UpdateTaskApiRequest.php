<?php

namespace App\Http\Requests\Api;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Field-worker task update from the mobile app.
 *
 * Unlike the web UpdateTaskRequest (which lets planners edit everything), the
 * mobile app is deliberately restricted to progress reporting: status,
 * percent complete, and hours worked. A worker can never reassign a task,
 * move it to another project, or rename it through this endpoint — those
 * fields are simply not accepted here.
 *
 * PATCH semantics: every field is optional ("sometimes"), so the client may
 * send only what changed. Whatever IS sent must still be valid.
 */
class UpdateTaskApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('accessApi', $this->route('task'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::in(Task::STATUSES)],
            'progress' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'actual_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');
        $progress = $this->input('progress');

        if ($status === Task::STATUS_COMPLETED && ! $this->has('progress')) {
            $this->merge(['progress' => 100]);
        } elseif ((int) $progress === 100 && ! $this->has('status')) {
            $this->merge(['status' => Task::STATUS_COMPLETED]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $status = $this->input('status');
            $progress = $this->input('progress');

            if ($status === Task::STATUS_COMPLETED && $progress !== null && (int) $progress !== 100) {
                $validator->errors()->add('progress', 'Completed tasks must have 100% progress.');
            }

            if ($status === Task::STATUS_CANCELLED && $progress !== null && (int) $progress !== 0) {
                $validator->errors()->add('progress', 'Cancelled tasks must have 0% progress.');
            }

            if ($progress !== null && (int) $progress === 100 && $status !== Task::STATUS_COMPLETED) {
                $validator->errors()->add('status', 'Tasks at 100% progress must be completed.');
            }
        });
    }
}
