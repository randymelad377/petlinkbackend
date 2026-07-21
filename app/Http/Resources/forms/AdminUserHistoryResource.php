<?php

namespace App\Http\Resources\forms;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $transaction_type_id = $this->transaction_type_id;
        $user = User::where("public_id", $request->route("id"))->first();

        $isUserOwner = $user->id === $this->pet_poster_id;

        $message = match ($transaction_type_id) {
            1 => $isUserOwner ? ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName) . " rehomed " . $this->pet->species->species .  " to " . ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName) :
                ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName) . " adopted " . $this->pet->species->species .  " to " . ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName),
            2 => $isUserOwner ? ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName) . " returned " . $this->pet->species->species .  " to " . ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName) :
                ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName) . " retrieved " . $this->pet->species->species .  " to " . ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName),
            3 => $isUserOwner ? ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName) . " retrieved " . $this->pet->species->species .  " to " . ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName) :
                ucfirst($this->pet_interested->firstName) . " " . ucfirst($this->pet_interested->lastName) . " returned " . $this->pet->species->species .  " to " . ucfirst($this->pet_poster->firstName) . " " . ucfirst($this->pet_poster->lastName),
        };

        return [
            "public_id" => $this->public_id,
            "user_id" => $isUserOwner ? $this->pet_poster_id : $this->pet_interested_id,
            "user_image" => $isUserOwner ? asset("storage/" . $this->pet_poster->user_img_path) : asset("storage/" . $this->pet_interested->user_img_path),

            "other_user_id" => $isUserOwner ?  $this->pet_interested->public_id : $this->pet_poster->public_id,
            "other_user_image" => $isUserOwner ? asset("storage/" . $this->pet_interested->user_img_path) : asset("storage/" . $this->pet_poster->user_img_path),

            "message" => $message
        ];
    }
}
