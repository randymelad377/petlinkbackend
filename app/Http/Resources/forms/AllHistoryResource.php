<?php

namespace App\Http\Resources\forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isUserLastOwner = $request->user()->id === $this->pet_poster_id;

        return [
            "public_id" => $this->public_id,
            "user" => $isUserLastOwner ? ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName) :
                ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName),
            "user_image" => $isUserLastOwner ? asset("storage/" . $this->pet_interested->user_img_path) : asset("storage/" . $this->pet_poster->user_img_path),
            "species" => $this->pet->species->species
        ];
    }
}
