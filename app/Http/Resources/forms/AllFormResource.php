<?php

namespace App\Http\Resources\forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $requestTo = $request->input("requestTo") === "my-request";

        return [
            "public_id" => $this->public_id,
            "user" => [
                "user_name" => $requestTo ? $this->pet->user->firstName . " " . $this->pet->user->lastName : $this->user->firstName . " " . $this->user->lastName,
                "user_image" => $requestTo ? asset("storage/" . $this->pet->user->user_img_path) : asset("storage/" . $this->user->user_img_path)
            ],
            "pet_species" =>  $this->pet->species->species
        ];
    }
}
