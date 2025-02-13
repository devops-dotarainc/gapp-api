<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'year' => ['string', 'min:4', 'max:4'],
            'contact_number' => ['string', 'min:11', 'max:11'],
            'position' => ['integer'],
            'island_group' => ['integer', 'in:1,2,3'],
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1', 'max:100'],
            'sort' => ['string'],
            'order' => ['string', 'in:asc,desc'],
            'search' => ['string', 'max:255'],
        ];
    }
}
