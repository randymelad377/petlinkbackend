<?php

namespace App\Http\Resources\forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function PHPUnit\Framework\isEmpty;

class FormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $answers = [];

        foreach (
            $this->user->answers()
                ->where('pet_id', $this->pet_id)
                ->whereHas('question', function ($q) {
                    $q->whereNull('history_id');
                })
                ->with('question')
                ->get() as $answer
        ) {
            $answers[] = [
                "question" => $answer->question?->question,
                "answer"   => $answer->answer,
            ];
        }

        return [
            "user" => [
                "user_id" => $user->id === $this->user_id ? $this->pet->user->public_id : $this->user->public_id,
                "user_name" => $user->id === $this->user_id ? ucfirst($this->pet->user->firstName) . " " . ucfirst($this->pet->user->lastName) : ucfirst($this->user->firstName) . " " . ucfirst($this->user->lastName),
                "user_image" => $user->id === $this->user_id ? asset("storage/" . $this->pet->user->user_img_path) : asset("storage/" . $this->user->user_img_path)
            ],
            "pet_id" => $this->pet->public_id,
            "answers" => $answers,
            "myRequest" => $user->id === $this->user->id,
            "hasTransaction" => empty($this->transaction),
            "isOwner" => $user->id !== $this->user_id
        ];
    }
}
