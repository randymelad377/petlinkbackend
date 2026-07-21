<?php

namespace App\Http\Resources\forms;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserTransactionResource extends JsonResource
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

        $user = User::where("public_id", $request->route("id"))->first();

        $isUserOwner = $user->id === $owner->id;

        $message = match ($pet->transaction_type_id) {
            1 => $isUserOwner ? ucfirst($owner->firstName) . " " . ucfirst($owner->lastName) . " rehoming " . $this->pet->species->species .  " to " . ucfirst($interested->firstName) . " " . ucfirst($interested->lastName) :
                ucfirst($interested->firstName) . " " . ucfirst($interested->lastName) . " adopting " . $this->pet->species->species .  " to " . ucfirst($owner->firstName) . " " . ucfirst($owner->lastName),
            2 => $isUserOwner ? ucfirst($owner->firstName) . " " . ucfirst($owner->lastName) . " returning " . $this->pet->species->species .  " to " . ucfirst($interested->firstName) . " " . ucfirst($interested->lastName) :
                ucfirst($interested->firstName) . " " . ucfirst($interested->lastName) . " retrieving " . $this->pet->species->species .  " to " . ucfirst($owner->firstName) . " " . ucfirst($owner->lastName),
            3 => $isUserOwner ? ucfirst($owner->firstName) . " " . ucfirst($owner->lastName) . " retrieving " . $this->pet->species->species .  " to " . ucfirst($interested->firstName) . " " . ucfirst($interested->lastName) :
                ucfirst($interested->firstName) . " " . ucfirst($interested->lastName) . " returning " . $this->pet->species->species .  " to " . ucfirst($owner->firstName) . " " . ucfirst($owner->lastName),
        };

        return [
            "public_id" => $this->public_id,
            "user_id" => $isUserOwner ? $owner->public_id : $interested->public_id,
            "user_image" => $isUserOwner ? asset("storage/" . $owner->user_img_path) : asset("storage/" . $interested->user_img_path),

            "other_user_id" => $isUserOwner ?  $interested->public_id : $owner->public_id,
            "other_user_image" => $isUserOwner ? asset("storage/" . $interested->user_img_path) : asset("storage/" . $owner->user_img_path),

            "message" => $message
        ];
    }
}
