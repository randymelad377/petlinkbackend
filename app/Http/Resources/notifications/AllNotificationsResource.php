<?php

namespace App\Http\Resources\notifications;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllNotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $title = $this->data["title"];

        $object_id = match ($title) {
            "newVerifiedPet" => "pet_id",
            "adminSendPet" => "pet_id",
            "adminSend" => "object_id",
            "newConcern" => "user_id",
            "modifyPet" => "pet_id",
            "acceptForm" => "transaction_id",
            "markAs" => "object_id",
            "requestForm" => "form_id",
            "newPetAdded" => "pet_id",
            "newUser" => "user_id",
            "approve" => "pet_id"
        };


        $data =  [
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'image' => $this->data['image_path'] !== "defaults/defaultPhp.png" ? asset("storage/" . $this->data['image_path']) : asset($this->data['image_path']),
            'created_at' => $this->created_at,
            'isRead' => (bool) $this->read_at,
            'id' => $this->id,
            "object_id" => $this->data[$object_id]
        ];

        if ($title == "markAs") {
            $data["haveHistory"] = $this->data["haveHistory"];
        }

        return $data;
    }
}
