<?php

namespace App\Http\Requests\Wingband;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        // return [
        //     'stag_registry' => ['nullable', 'string', 'max:255'],
        //     'breeder_name' => ['nullable', 'string', 'max:255'],
        //     'farm_name' => ['nullable', 'string', 'max:255'],
        //     'farm_address' => ['nullable', 'string', 'max:255'],
        //     'province' => ['nullable', 'string', 'max:255'],
        //     'wingband_number' => ['nullable', 'integer', 'min:1'],
        //     'feather_color' => ['nullable', 'string', 'max:255'],
        //     'leg_color' => ['nullable', 'string', 'max:255'],
        //     'comb_shape' => ['nullable', 'string', 'max:255'],
        //     'nose_markings' => ['nullable', 'string', 'max:255'],
        //     'feet_markings' => ['nullable', 'string', 'max:255'],
        //     'status' => ['nullable', 'integer', 'min:1', 'in:1,2'],
        // ];

        return [
            'wingband_data' => ['required', 'array'],
        ];
    }
}
