<?php

namespace App\Http\Requests\Wingband;

use Illuminate\Foundation\Http\FormRequest;

class ImportWingbandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ];
    }
}
