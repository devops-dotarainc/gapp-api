<?php

namespace App\Http\Requests\Setting;

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
            'address' => ['string'],
            'email' => ['email'],
            'telephone_number' => ['string'],
            'twitter_url' => ['string'],
            'youtube_url' => ['string'],
            'facebook_url' => ['string'],
            'linkedin_url' => ['string'],
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1', 'max:100'],
            'sort' => ['string'],
            'order' => ['string', 'in:asc,desc'],
            'search' => ['string', 'max:255'],
        ];
    }
}
