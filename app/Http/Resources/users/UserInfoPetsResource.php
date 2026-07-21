<?php

namespace App\Http\Resources\users;

use App\Http\Resources\pets\AllPetResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoPetsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            "user_info" => [
                "public_id" => $this->public_id,
                "name" => $this->firstName . " " . $this->lastName,
                'image' => $this->user_img_path !== "defaults/defaultPhp.png"  ? asset("storage/" . $this->user_img_path) : asset("defaults/" . "defaultPhp.png"),
            ],
            "user_pets" => AllPetResource::collection($this->pets)
        ];
    }
}
