<?php

namespace App\Http\Resources\forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAllTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $owner = $this->pet->user;
        $interested = $this->user;
        $pet = $this->pet;

        $message = match ($this->pet->transaction_type_id) {
            1 => ucfirst($owner->firstName) . " " . ucfirst($owner->lastName) . " rehoming " . $this->pet->species->species . " to " . ucfirst($owner->firstName) . " " . ucfirst($owner->lastName),
            2 => ucfirst($owner->firstName) . " " . ucfirst($owner->lastName) . " returning " . $this->pet->species->species . " to " . ucfirst($owner->firstName) . " " . ucfirst($owner->lastName),
            3 => ucfirst($owner->firstName) . " " . ucfirst($owner->lastName) . " retrieving " . $this->pet->species->species . " to " . ucfirst($owner->firstName) . " " . ucfirst($owner->lastName),
        };

        return [
            "public_id" => $this->public_id,
            "owner_id" => $owner->public_id,
            "owner_image" => asset("storage/" . $owner->user_img_path),
            "pet_species" => strtoupper($pet->species->species),
            "interested_id" => $interested->id,
            "interested_image" => asset("storage/" . $interested->user_img_path),
            "message" => $message,
        ];
    }
}
