<?php

namespace App\Http\Requests\pets;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePetsRequest extends FormRequest
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
        $transaction_type_id = $this->input("transaction_type_id");

        $pet_info = [
            "transaction_type_id" => "required|integer|exists:transaction_types,id",
            'species' => ['required', 'string', "regex:/^[A-Za-z\s'.,-]+$/"],
            'breed' => ['required', 'string', "regex:/^[A-Za-z\s'.,-]+$/"],
            "color" => "required|string",
            "gender" => "required|in:male,female,Female,Male",
            'age' => ['required', 'regex:/^\d+\s?(year|years|month|months)$/i'],

            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',

            'questions' => 'required|array|size:3',
            'questions.*' => 'required|string|min:2|max:255',

            "description" => "required|string",
        ];

        switch ($transaction_type_id) {

            case 1: //FOR REHOME (1)
                $pet_info["medical_record"] = "nullable|string|min:3|max:255";
                $pet_info["diagnosis"] = "nullable|string|min:3|max:255";
                $pet_info["vaccine_record"] = "nullable|string|min:3|max:255";
                $pet_info["current_medicines"] = "nullable|string|min:3|max:255";
                break;

            case 2: // Found
                $pet_info["age"] = ['nullable', 'regex:/^\d+\s?(year|years|month|months)$/i'];
                $pet_info["date_found"] = 'required|date|before_or_equal:today';
                $pet_info["found_at"] = "required|string|min:3|max:255";
                break;

            case 3: // Missing
                $pet_info["date_lost"] = 'required|date|before_or_equal:today';
                $pet_info["lost_at"] = "required|string|min:3|max:255";
                break;
        }

        return $pet_info;
    }
}
