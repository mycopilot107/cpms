<?php

namespace App\Http\Requests;

use App\Models\BoqItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Step 1 of the BOQ import: the project and the Excel/CSV file. */
class BoqImportUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', BoqItem::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', Rule::exists('projects', 'id')->where('company_id', $this->user()->company_id)],
            'file' => ['required', 'file', 'max:10240', 'extensions:xlsx,csv,txt'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.extensions' => 'Upload an Excel .xlsx or a .csv file. For an old .xls file, open it in Excel and use File → Save As → Excel Workbook (.xlsx).',
            'file.max' => 'The file is larger than 10 MB. Remove pictures or split the BOQ into smaller files.',
            'file.uploaded' => 'The file could not be uploaded. It may be larger than the server allows.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['project_id' => 'project'];
    }
}
