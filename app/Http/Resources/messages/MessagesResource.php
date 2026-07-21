<?php

namespace App\Http\Resources\messages;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isCurrentUserMessage = $request->user()->id === $this->sender_id;

        return [
            "id" => $this->id,
            "isCurrentUserMessage" => $isCurrentUserMessage,
            "message" => $this->message,
            "image" => $this->image_message_path ? asset("storage/" . $this->image_message_path) : null,
            "created_at" => Carbon::parse($this->created_at)->format('y-m-d h-i A')
        ];
    }
}
