<?php

namespace App\Http\Resources\pets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllPetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->images()->first();

        return [
            "public_id" => $this->public_id,
            "pet_image" => $image ? asset("storage/{$image->image_path}") : null,
            "pet_transaction_type" => strtoupper($this->transaction_type->transaction_type),
            "pet_status" => strtoupper($this->status->status),
            "pet_created_at" => $this->created_at->format('Y-m-d'),
            "pet_species" => strtoupper($this->species->species),
            "location" => $this->transaction_type_id === 2
                ? $this->found->found_at
                : ($this->transaction_type_id === 3
                    ? $this->missing->lost_at
                    : null)
        ];
    }
}
