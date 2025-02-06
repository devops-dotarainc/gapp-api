<?php

namespace App\Http\Requests\ActivityLog;

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
            'sort' => ['string', 'in:id,created_at', 'max:255'],
            'order' => ['string', 'in:asc,desc', 'max:255'],
            'search' => ['string', 'max:255'],
            
            'username' => ['string', 'max:255'],
            'description' => ['string', 'max:255'],
            'status' => ['string'],
            'role' => ['string'],
            'date' => ['date_format:Y-m-d'],
            'from' => ['date_format:Y-m-d'],
            'to' => ['date_format:Y-m-d'],
        ];
    }
}
