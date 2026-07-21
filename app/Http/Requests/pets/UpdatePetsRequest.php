<?php

namespace App\Http\Requests\pets;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePetsRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'breed' => ['required', 'string', "regex:/^[A-Za-z\s'.,-]+$/"],
            "color" => "required|string",
            "gender" => "required|in:male,female,Female,Male",
            'age' => ['required', 'regex:/^\d+\s?(year|years|month|months)$/i'],

            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }
}
