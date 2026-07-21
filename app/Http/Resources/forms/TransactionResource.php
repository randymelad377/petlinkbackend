<?php

namespace App\Http\Resources\forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $transaction_type_id = $this->pet->transaction_type_id;

        $transaction = [
            "pet_info" => [
                "species" => $this->pet->species->species,
                "breed" => $this->pet->breed->breed,
                "gender" => $this->pet->gender,
                "color" => $this->pet->color,
                "age" => $this->pet->age,
            ],
            'pet_images' => $this->pet->images->map(function ($image) {
                return asset('storage/' . $image->image_path);
            })->toArray(),
            "pet_status" => $this->pet->status->status,
            "pet_transaction" => $this->pet->transaction_type_id,
            "form_public_id" => $this->form->public_id,
            "started_at" => $this->created_at->format('Y-m-d')
        ];

        switch ($transaction_type_id) {
            case 1:
                $transaction["pet_other_info"] = [
                    "description" => $this->pet->rehome->description,
                    "medical_record" => $this->pet->rehome->medical_record,
                    "diagnosis" => $this->pet->rehome->diagnosis,
                    "vaccine_records" => $this->pet->rehome->vaccine_records,
                    "current_medicines" => $this->pet->rehome->current_medicines,
                ];
                break;
            case 2:
                $transaction["pet_other_info"] = [
                    "description" => $this->pet->found->description,
                    "date_found" => $this->pet->found->date_found,
                    "found_at" => $this->pet->found->found_at
                ];
                break;
            case 3:
                $transaction["pet_other_info"] = [
                    "description" => $this->pet->missing->description,
                    "date_lost" => $this->pet->missing->date_lost,
                    "lost_at" => $this->pet->missing->lost_at,
                ];
                break;
            default:
                null;
        }

        $user = $request->user();

        $transaction["user"] = [
            "user_name" => $user->id === $this->user_id ? ucfirst($this->pet->user->firstName) . " " . ucfirst($this->pet->user->lastName) : ucfirst($this->user->firstName) . " " . ucfirst($this->user->lastName),
            "user_image" => $user->id === $this->user_id ? asset("storage/" . $this->pet->user->user_img_path) : asset("storage/" . $this->user->user_img_path),
            "user_id" => $user->id === $this->user_id ? $this->pet->user->public_id :  $this->user->public_id,
            "responded" => $user->id === $this->user_id ? $this->requesterMarkAsDone :  $this->ownerMarkAsDone
        ];

        return $transaction;
    }
}
