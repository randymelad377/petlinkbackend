<?php

namespace App\Http\Controllers\otherPetInfo;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\pets\UpdateOtherPetInfoRequest;
use App\Models\pet\Pets;
use App\Models\transactions\Questions;
use App\Services\PetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtherPetInfoController extends Controller
{
    public function update_questions(Request $request)
    {
        $fields = $request->validate([
            'questions' => 'required|array|size:3',
            'questions.*.question_id' => 'required|integer|exists:questions,id',
            'questions.*.question' => 'required|string|min:2|max:255',
        ]);


        $result = DB::transaction(function () use ($fields) {

            $updated = [];

            foreach ($fields['questions'] as $item) {
                $question = Questions::find($item['question_id']);

                if ($question) {
                    $question->update([
                        'question' => $item['question']
                    ]);

                    $updated[] = $question->fresh();
                }
            }

            return $updated;
        });

        return ApiResponse::success($result, "Questions updated.", 200);
    }

    public function update_other_pet_info(UpdateOtherPetInfoRequest $request, PetService $service)
    {
        $fields = $request->validated();
        $pet = Pets::where("public_id", $fields["pet_public_id"])->firstOrFail();

        if ($pet->pet_status_id !== 1 || $pet->pet_status_id !== 2) {
            return ApiResponse::error("You are only able to edit you pet when pending or not available for other users.");
        }
        $result = $service->updateOtherPetInfo($fields, $pet);

        if ($result["changed"]) {
            return ApiResponse::success($result["data"], $result["message"], 200);
        }

        return ApiResponse::error($result["message"], 409, null);
    }
}
