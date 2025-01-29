<?php

namespace App\Http\Requests\HallOfFame;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth()->user()->role->value === 1) {
            return true;
        }

        return false;
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
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:10240'],
            'event_date' => ['date_format:Y-m-d'],
        ];
    }
}
