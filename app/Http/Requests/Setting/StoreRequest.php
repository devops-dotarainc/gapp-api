<?php

namespace App\Http\Requests\Setting;

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
            'address' => ['string'],
            'telephone_number' => ['string'],
            'email' => ['email'],
            'twitter_url' => ['string'],
            'facebook_url' => ['string'],
            'youtube_url' => ['string'],
            'linkedin_url' => ['string'],
        ];
    }
}
