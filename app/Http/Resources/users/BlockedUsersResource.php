<?php

namespace App\Http\Resources\users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockedUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "user_image" => asset("storage/" . $this->blocked_user->user_img_path),
            "user_name" => ucfirst($this->blocked_user->firstName) . " " . ucfirst($this->blocked_user->lastName),
            "id" => $this->id
        ];
    }
}
