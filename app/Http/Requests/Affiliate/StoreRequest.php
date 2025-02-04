<?php

namespace App\Http\Requests\Affiliate;

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
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:10240'],
            'location' => ['string'],
            'name' => ['string', 'required'],
            'contact_number' => ['string', 'min:11', 'max:11'],
            'island_group' => ['integer', 'in:1,2,3'],
            'position' => ['integer'],
        ];
    }
}
