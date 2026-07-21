<?php

namespace App\Http\Resources\forms;

use App\Http\Resources\pets\VerifiedPetResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isUserPoster = $this->pet_poster_id === $user->id;
        $type = $this->transaction_type_id;

        $otherPetInfo = match ($type) {
            1 => [
                "description" => $this->rehome?->description,
                "current_medicines" => $this->rehome?->current_medicines,
                "diagnosis" => $this->rehome?->diagnosis,
                "medical_record" => $this->rehome?->medical_record,
                "vaccine_records" => $this->rehome?->vaccine_records,
            ],
            2 => [
                "description" => $this->found?->description,
                "date_found" => $this->found?->found_at,
                "found_at" => $this->found?->found_at,
            ],
            3 => [
                "description" => $this->missing?->description,
                "date_lost" => $this->missing?->date_lost,
                "lost_at" => $this->missing?->lost_at,
            ],
            default => null,
        };

        $posterName = ucfirst($this->pet_poster?->firstName ?? '') . ' ' . ucfirst($this->pet_poster?->lastName ?? '');
        $interestedName = ucfirst($this->pet_interested?->firstName ?? '') . ' ' . ucfirst($this->pet_interested?->lastName ?? '');

        $posterImage = asset("storage/" . $this->pet_poster?->user_img_path);
        $interestedImage = asset("storage/" . $this->pet_interested?->user_img_path);

        $isPoster = $isUserPoster;

        $userName = $isPoster ? $interestedName : $posterName;
        $userId = $isPoster ? $this->pet_interested->public_id : $this->pet_poster->public_id;
        $userImage = $isPoster ? $interestedImage : $posterImage;

        $label = match (true) {
            $isPoster && $type === 1 => "You rehomed this pet to {$interestedName}",
            $isPoster && $type === 2 => "You returned this pet to {$interestedName}",
            $isPoster && $type === 3 => "You retrieved this pet from {$interestedName}",

            !$isPoster && $type === 1 => "{$posterName} rehomed this pet to you.",
            !$isPoster && $type === 2 => "{$posterName} returned this pet to you.",
            !$isPoster && $type === 3 => "{$posterName} retrieved this pet to you.",

            default => null,
        };

        return [
            "other_pet_info" => $otherPetInfo,
            "questions" => QuestionsResource::collection($this->questions),
            "pet_info" => new VerifiedPetResource($this->pet),
            "user" => trim($userName),
            "user_id" => $userId,
            "image" => $userImage,
            "created_at" => $this->created_at?->format('Y-m-d'),
            "label" => $label,
        ];
    }
}
