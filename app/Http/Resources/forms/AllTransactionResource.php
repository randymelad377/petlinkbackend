<?php

namespace App\Http\Resources\forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            "public_id" => $this->public_id,
            "user_name" => $this->user->id === $user->id ? ucfirst($this->pet->user->firstName) . " " . ucfirst($this->pet->user->lastName) : ucfirst($this->user->firstName) . " " . ucfirst($this->user->lastName),
            "user_image" => $this->user->id === $user->id ? asset("storage/" . $this->pet->user->user_img_path) : asset("storage/" . $this->user->user_img_path),
            "pet_species" => strtoupper($this->pet->species->species)
        ];
    }
}
