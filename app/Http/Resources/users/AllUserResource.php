<?php

namespace App\Http\Resources\users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            "public_id" => $this->public_id,
            "name" => $this->firstName . " " . $this->lastName,
            "status" => $this->user_status_id,
            'image' => $this->user_img_path !== "defaults/defaultPhp.png"  ? asset("storage/" . $this->user_img_path) : asset("defaults/" . "defaultPhp.png")
        ];
    }
}
