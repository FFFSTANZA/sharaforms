<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => 'required|string|in:typeform,tally,fillout,google_forms',
            'import_data' => 'required|array',
            'import_data.url' => 'nullable|url|required_without:import_data.form_id',
            'import_data.form_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'import_data.url.required' => 'A form URL is required.',
            'import_data.url.required_without' => 'A form URL is required.',
            'import_data.url.url' => 'Please provide a valid URL.',
        ];
    }
}
