<?php

namespace App\Http\Resources\forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAllHistory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $transaction_type_id = $this->transaction_type_id;

        $message = match ($transaction_type_id) {
            1 => ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName) . " rehomed " . $this->pet->species->species .  " to " . ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName),
            2 => ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName) . " returned " . $this->pet->species->species .  " to " . ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName),
            3 => ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName) . " retrieved " . $this->pet->species->species .  " to " . ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName),
        };

        return [
            "public_id" => $this->public_id,
            "pet_poster_id" => $this->pet_poster->public_id,
            "pet_poster_image" => asset("storage/" . $this->pet_poster->user_img_path),
            "pet_interested_id" => $this->pet_interested->public_id,
            "pet_interested_image" => asset("storage/" . $this->pet_interested->user_img_path),
            "message" => $message
        ];
    }
}
