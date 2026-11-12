<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

/** The Excel or CSV file for a project import. */
class ProjectImportUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            'file.max' => 'The file is larger than 10 MB.',
            'file.uploaded' => 'The file could not be uploaded. It may be larger than the server allows.',
        ];
    }
}
