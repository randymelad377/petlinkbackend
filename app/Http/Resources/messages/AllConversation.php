<?php

namespace App\Http\Resources\messages;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllConversation extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $receiver = $request->user()->id === $this->user1_id ? $this->user2 : $this->user1;
        $user = $request->user();
        $haveMessages = $this->messages()->where("sender_id", $user->id)->where("deleted_by_sender", false)->count();
        $haveMessages2 = $this->messages()->where("sender_id", $receiver->id)->where("deleted_by_receiver", false)->count();
        return [
            "id1" => $user->id,
            "messages_count" => $haveMessages + $haveMessages2,
            "public_id" => $this->public_id,
            "receiver_id" => $receiver->public_id,
            "user_name" => $user->id === $this->user1_id ? ucfirst($this->user2->firstName) . " " . ucfirst($this->user2->lastName) :
                ucfirst($this->user1->firstName) . " " . ucfirst($this->user1->lastName),

            "user_image" => $user->id === $this->user1_id ? asset("storage/" . $this->user2->user_img_path) :
                asset("storage/" . $this->user1->user_img_path)
        ];
    }
}
