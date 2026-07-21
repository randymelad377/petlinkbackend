<?php

namespace App\Http\Resources\pets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $transaction_type_id = $this->transaction_type_id;

        $pet_info = [
            "species" => $this->species->species,
            "breed" => $this->breed->breed,
            "color" => $this->color,
            "gender" => $this->gender,
            "age" => $this->age,
        ];

        $pet_questions = [];
        foreach ($this->questions as $question) {
            $pet_questions[] = [
                "question_id" => $question->id,
                "question" => $question->question
            ];
        }

        $pet_images = [];
        foreach ($this->images as $image) {
            $pet_images[] = asset("storage/" . $image->image_path);
        }

        $pet_transaction_info = null;

        switch ($transaction_type_id) {
            case 1:
                $pet_transaction_info = [
                    "description" => $this->rehome->description,
                    "medical_record" => $this->rehome->medical_record,
                    "diagnosis" => $this->rehome->diagnosis,
                    "vaccine_records" => $this->rehome->vaccine_records,
                    "current_medicines" => $this->rehome->current_medicines,
                ];
                break;
            case 2:
                $pet_transaction_info = [
                    "description" => $this->found->description,
                    "date_found" => $this->found->date_found,
                    "found_at" => $this->found->found_at
                ];
                break;
            case 3:
                $pet_transaction_info = [
                    "description" => $this->missing->description,
                    "date_lost" => $this->missing->date_lost,
                    "lost_at" => $this->missing->lost_at,
                ];
                break;
            default:
                null;
        }

        $pet_owner = [
            "public_id" => $this->user->public_id,
            "user_name" => $this->user->firstName . " " . $this->user->lastName,
            "user_image" => $this->user->user_img_path !== "defaults/defaultPhp.png"  ? asset("storage/" . $this->user->user_img_path) : asset("defaults/" . "defaultPhp.png"),
        ];

        $pet_others = [
            "transaction_type_id" => $this->transaction_type_id,
            "status" => $this->status->status
        ];


        return [
            "pet_info" => $pet_info,
            "pet_questions" => $pet_questions,
            "pet_images" => $pet_images,
            "pet_transaction_info" => $pet_transaction_info,
            "pet_owner" => $pet_owner,
            "pet_others" => $pet_others
        ];
    }
}
