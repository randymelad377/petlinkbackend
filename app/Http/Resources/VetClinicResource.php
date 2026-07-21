<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VetClinicResource extends JsonResource
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
            "clinic_name" => $this->clinic_name,
            "coord" => [$this->latitude, $this->longitude],
            "isOpen" => $this->isOpen
        ];
    }
}
