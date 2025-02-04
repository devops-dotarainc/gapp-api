<?php

namespace App\Http\Requests\Wingband;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'limit' => ['integer', 'min:0', 'max:100'],
            'sort' => ['string', 'in:id,created_at,stag_registry', 'max:255'],
            'order' => ['string', 'in:asc,desc', 'max:255'],
            'season' => ['string', 'in:1,2,3,4'],
            'search' => ['string', 'max:255'],
            'stag_registry' => ['string', 'max:255'],
            'breeder_name' => ['string', 'max:255'],
            'wingband_number' => ['string', 'max:255'],
            'chapter' => ['string', 'max:255'],
            'updated_by' => ['string', 'max:255'],
            'updated_by' => ['string', 'in:1,2'],
            'wingband_year' => ['digits:4', 'integer', 'min:1900', 'max:'.date('Y')+1],
            'encoder' => ['string', 'max:255'],
        ];
    }
}
