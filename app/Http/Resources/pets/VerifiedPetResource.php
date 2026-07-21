<?php

namespace App\Http\Resources\pets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerifiedPetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pet_info = [
            "species" => $this->species->species,
            "breed" => $this->breed->breed,
            "color" => $this->color,
            "gender" => $this->gender,
            "age" => $this->age,
            "statusVerified" => $this->pet_status_id == 2
        ];

        $pet_images = [];
        foreach ($this->images as $image) {
            $pet_images[] = asset("storage/" . $image->image_path);
        }

        return [
            "pet_info" => $pet_info,
            "pet_images" => $pet_images
        ];
    }
}
