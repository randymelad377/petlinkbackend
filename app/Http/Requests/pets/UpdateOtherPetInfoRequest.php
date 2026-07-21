<?php

namespace App\Http\Requests\pets;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOtherPetInfoRequest extends FormRequest
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

        $transaction_info = [
            "pet_public_id" => "required|string",
            "description" => "required|string",
            "transaction_type_id" => "required|integer|exists:transaction_types,id",
        ];

        switch ($transaction_type_id) {

            case 1: //FOR REHOME (1)
                $transaction_info["medical_record"] = "nullable|string|min:3|max:255";
                $transaction_info["diagnosis"] = "nullable|string|min:3|max:255";
                $transaction_info["vaccine_records"] = "nullable|string|min:3|max:255";
                $transaction_info["current_medicines"] = "nullable|string|min:3|max:255";
                break;

            case 3: // Missing
                $transaction_info["date_lost"] = 'nullable|date|before_or_equal:today';
                $transaction_info["lost_at"] = "nullable|string|min:3|max:255";
                break;

            case 2: // Found
                $transaction_info["date_found"] = 'nullable|date|before_or_equal:today';
                $transaction_info["found_at"] = "nullable";
                break;
        }

        return $transaction_info;
    }
}
