<?php

namespace App\Services;

use App\Events\pets\forms\AcceptFormEvent;
use App\Events\pets\forms\RequestFormEvent;
use App\Models\forms\Form;
use App\Models\forms\Transaction;
use App\Models\pet\Pets;
use App\Models\transactions\Answers;
use App\Models\transactions\Questions;
use Illuminate\Support\Facades\DB;

class FormService
{
    public function create($fields, $user)
    {
        $result = DB::transaction(function () use ($fields, $user) {
            $pet = Pets::with("user")->where('public_id', $fields['pet_public_id'])->firstOrFail();
            $answers = $fields["answers"];
            $formExist = Form::where("user_id", $user->id)
                ->where("pet_id", $pet->id)
                ->exists();

            if ($formExist) {
                return [
                    "data" => null,
                    "message" => "You already request."
                ];
            }

            $form = $user->forms()->create([
                "pet_id" => $pet->id
            ]);

            foreach ($answers as $answer) {
                Answers::create([
                    "pet_id" => $pet->id,
                    "user_id" => $user->id,
                    "question_id" => $answer["question_id"],
                    "answer" => $answer["answer"]
                ]);
            }

            RequestFormEvent::dispatch($form, $user, $pet->user);

            return [
                "data" => $form,
                "message" => "Requested."
            ];
        });

        return $result;
    }

    public function update($action, $form, $pet, $user)
    {
        $result = DB::transaction(function () use ($action, $form, $pet, $user) {

            if ($user->id === $form->user_id && !$action) {
                $questions = Questions::where("pet_id", $pet->id)->get();

                foreach ($questions as $question) {
                    $question->answers()->where("user_id", $form->user_id)->delete();
                }

                $form->delete();
                return [
                    "data" => null,
                    "message" => "Form cancelled",
                    "code" => 200,
                    "success" => true
                ];
            } else if ($user->id === $pet->user_id) {
                if ($action) {
                    $transaction = Transaction::create([
                        "pet_id" => $pet->id,
                        "user_id" => $form->user_id,
                        "form_id" => $form->id,
                        "transaction_status_id" => 1
                    ]);

                    $questions = $pet->questions;

                    foreach ($questions as $question) {
                        $question->answers()->where("user_id", "!=", $form->user_id)->delete();
                    }

                    $pet->forms()
                        ->where('id', '!=', $form->id)
                        ->delete();

                    $pet->update(["pet_status_id" => 9]);

                    AcceptFormEvent::dispatch($transaction, $user, $form->user, true);

                    return [
                        "data" => $transaction,
                        "message" => "Transaction started",
                        "code" => 200,
                        "success" => true
                    ];
                }

                $questions = $pet->questions;

                foreach ($questions as $question) {
                    $question->answers()->where("user_id", $form->user_id)->delete();
                }

                AcceptFormEvent::dispatch(null, $user, $form->user, false);

                $form->delete();

                return [
                    "data" => null,
                    "message" => "Request cancelled",
                    "code" => 200,
                    "success" => true
                ];
            } else {
                return [
                    "data" => null,
                    "message" => "Invalid Action.",
                    "code" => 403,
                    "success" => false
                ];
            }
        });

        return $result;
    }
}
