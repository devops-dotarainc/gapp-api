<?php

namespace App\Http\Requests\Schedule;

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
            'month' => ['date_format:Y-m'],
            'title' => ['string'],
            'background_color' => ['string'],
            'description' => ['string'],
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1', 'max:100'],
            'sort' => ['string'],
            'order' => ['string', 'in:asc,desc'],
            'search' => ['string', 'max:255'],
        ];
    }
}
