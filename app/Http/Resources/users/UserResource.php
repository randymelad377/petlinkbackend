<?php

namespace App\Http\Resources\users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "firstName" => ucfirst($this->firstName),
            "middleName" => $this->middleName,
            "lastName" => ucfirst($this->lastName),
            "gender" => $this->gender,
            "age" => $this->age,
            "houseNumber" => $this->houseNumber,
            "street" => $this->street,
            "barangay" => $this->barangay,
            "municipality" => $this->municipality,
            "username" => $this->username,
            "email" => $this->email,
            "contactNumber" => $this->contactNumber,
            "status" => $this->status->user_status,
            "role" => $this->role->role,
            "image" => $this->user_img_path === "defaults/defaultPhp.png" ? asset($this->user_img_path) : asset("storage/" . $this->user_img_path),
            "warningCount" => $this->warningCount
        ];
    }
}
