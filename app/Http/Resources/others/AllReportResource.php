<?php

namespace App\Http\Resources\others;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "report_id" => $this->id,
            "reporter_name" => ucfirst($this->reporter->firstName) . " " . ucfirst($this->reporter->lastName),
            "reporter_image" => asset("storage/" . $this->reporter->user_img_path),
            "reporter_id" => $this->reporter->public_id,
            "user_name" => ucfirst($this->user->firstName) . " " . ucfirst($this->user->lastName),
            "user_image" => asset("storage/" . $this->user->user_img_path),
            "user_id" => $this->user->public_id,
            "message" => $this->message,
            "image" => asset("storage/" . $this->image_path),
            "isRead" => $this->isRead,
            "created_at" => $this->created_at
        ];
    }
}
