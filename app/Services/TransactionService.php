<?php

namespace App\Services;

use App\Events\pets\forms\MarkAsEvent;
use App\Models\forms\History;
use App\Models\pet\PetPreviousOwner;
use App\Models\pet\Pets;
use App\Models\transactions\Answers;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function update($action, $transaction, $user)
    {

        $result = DB::transaction(function () use ($action, $transaction, $user) {

            $pet = Pets::where("id", $transaction->pet_id)->firstOrFail();
            $history = null;
            $column = $transaction->user_id === $user->id
                ? "requesterMarkAsDone"
                : ($pet->user_id === $user->id
                    ? "ownerMarkAsDone"
                    : "");

            if (!$column) {
                return [
                    "data" => null,
                    "message" => "Action not permitted",
                    "success" => false,
                    "code" => 403
                ];
            }

            $transaction->update([$column => $action]);
            $transaction->refresh();

            if ($transaction->ownerMarkAsDone && $transaction->requesterMarkAsDone) {
                $history = History::create([
                    "transaction_type_id" => $pet->transaction_type_id,
                    "pet_poster_id" => $pet->user_id,
                    "pet_interested_id" => $transaction->user_id,
                    "pet_id" => $pet->id,
                    "transaction_started" => $transaction->form->created_at
                ]);

                foreach ($pet->questions as $question) {
                    $question->update(["pet_id" => null]);
                    $question->update(["history_id" => $history->id]);
                }

                if ($pet->transaction_type_id === 1) {
                    $pet->rehome()->update(["history_id" => $history->id, "pet_id" => null],);
                } else if ($pet->transaction_type_id === 2) {
                    $pet->found()->update(["history_id" => $history->id, "pet_id" => null]);
                } else if ($pet->transaction_type_id === 3) {
                    $pet->missing()->update(["history_id" => $history->id, "pet_id" => null]);
                }

                $newOwnerId = $pet->transaction_type_id === 3 ? $pet->user_id : $transaction->user_id;

                $pet->update(["pet_status_id" => 2, "transaction_type_id" => 4, "user_id" => $newOwnerId]);

                $sender = $user->id === $transaction->user->id ? $transaction->user : $transaction->pet->user;
                $receiver = $user->id === $transaction->user->id ? $transaction->pet->user : $transaction->user;

                $transaction->form->delete();
                $transaction->delete();

                MarkAsEvent::dispatch($history, null, $history->pet_poster, $history->pet_interested, true);

                return [
                    "data" => [
                        "history" => $history,
                        "previousOwner" => isset($previousOwner) ? $previousOwner : null
                    ],
                    "message" => "History added.",
                    "success" => true,
                    "code" => 200
                ];
            } else if (
                $transaction->ownerMarkAsDone !== null &&
                $transaction->requesterMarkAsDone !== null &&
                !$transaction->ownerMarkAsDone && !$transaction->requesterMarkAsDone
            ) {

                $newStatusId = match ($pet->transaction_type_id) {
                    1 => 3,
                    2 => 4,
                    3 => 5
                };

                MarkAsEvent::dispatch(null, null, $transaction->pet->user, $transaction->user, true);

                $pet->update(["pet_status_id" => $newStatusId]);
                Answers::where("pet_id", $transaction->pet_id)->where("user_id", $transaction->user_id)->delete();
                $transaction->form->delete();
                $transaction->delete();

                return [
                    "data" => [$transaction->pet->user, $transaction->user],
                    "message" => "Transaction Cancelled",
                    "success" => true,
                    "code" => 200
                ];
            } else if (
                $transaction->ownerMarkAsDone !== null &&
                $transaction->requesterMarkAsDone !== null &&
                $transaction->ownerMarkAsDone !== $transaction->requesterMarkAsDone
            ) {
                $transaction->update([
                    "requesterMarkAsDone" => null,
                    "ownerMarkAsDone" => null
                ]);

                $transaction->refresh();

                $sender = $user->id === $transaction->user->id ? $transaction->user : $transaction->pet->user;
                $receiver = $user->id === $transaction->user->id ? $transaction->pet->user : $transaction->user;

                MarkAsEvent::dispatch(null, $transaction, $sender, $receiver, true);

                return [
                    "data" => $transaction,
                    "message" => "You have conflict action with other participant and action must be repeat.",
                    "success" => true,
                    "code" => 200
                ];
            } else {

                $receiver = $user->id === $transaction->user_id ? $transaction->pet->user : $transaction->user;

                MarkAsEvent::dispatch(null, $transaction, $user, $receiver, $action);
            }

            return [
                "data" => $transaction,
                "message" => $action ? "Mark as done" : "Mark as cancelled",
                "success" => true,
                "code" => 200
            ];
        });

        return $result;
    }
}
