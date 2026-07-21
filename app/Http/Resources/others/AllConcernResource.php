<?php

namespace App\Http\Resources\others;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllConcernResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "user_id" => $this->user->public_id,
            "user_name" => ucfirst($this->user->firstName) . " " . ucfirst($this->user->lastName),
            "user_image" => asset("storage/" . $this->user->user_img_path),
            "concern_id" => $this->id,
            "message" => $this->message,
            "image" => asset("storage/" . $this->image_path),
            "isRead" => $this->isRead
        ];
    }
}
