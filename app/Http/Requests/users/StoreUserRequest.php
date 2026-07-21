<?php

namespace App\Http\Requests\users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            "firstName" => "required|min:3|regex:/^[A-Za-z\s]+$/",
            "middleName" => "required|min:1|regex:/^[A-Za-z\s]+$/",
            "lastName" => "required|min:3|regex:/^[A-Za-z\s]+$/",
            "gender" => "required|in:male,female,Male,Female",
            "age" => "required||integer|digits:2",
            "contactNumber" => "required|min:11|max:11|regex:/^09\d{9}$/",
            "houseNumber" => "required|min:1",
            "street" => "required|min:3",
            "barangay" => "required|min:3",
            "email" => "required|email|unique:users",
            "username" => "required|unique:users|regex:/^[A-Za-z](?=.*[\d\W]).{9}$/",
            "password" => "required|confirmed",
            "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048"
        ];
    }
}
