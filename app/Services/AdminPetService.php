<?php

namespace App\Services;

use App\Events\pets\admin\ModifyPetEvent;
use App\Events\pets\admin\NewVerifiedPetEvent;
use App\Models\transactions\Answers;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

use function PHPUnit\Framework\isEmpty;

class AdminPetService
{

    public function modifyPet($action, $pet)
    {

        $result = DB::transaction(function () use ($action, $pet) {
            $petUpdated = null;
            //PET CURRENT TRANSCTION TYPE
            $petTransactionType = $pet->transaction_type->id;

            //NEW PET FOR REHOMING/FOUND/MISSING ONLY
            $transactionTypeStatus = match ($petTransactionType) {
                1 => 3,
                2 => 4,
                3 => 5,
                default => 4
            };

            //NEW STATUS ID
            $newStatusId = match ($action) {
                "approve" => $transactionTypeStatus,
                "cancelTransaction" => $transactionTypeStatus,
                "softDelete" => 10,
                "delete" => 0,
                "decline" => 0,
                "back" => 2,
                default => throw new InvalidArgumentException('Invalid action'),
            };

            if ($newStatusId == 0) {

                ModifyPetEvent::dispatch($pet, $pet->user, $action, null);

                foreach ($pet->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                }

                $pet->images()->delete();

                if ($pet->transaction_type_id === 1) {
                    $pet->rehome->delete();
                } else if ($pet->transaction_type_id === 2) {
                    $pet->found->delete();
                } else if ($pet->transaction_type_id === 3) {
                    $pet->missing->delete();
                }

                $pet->questions()->delete();
                $pet->answers()->delete();
                $pet->histories()->delete();
                $pet->previousOwners()->delete();

                $pet->delete();

                $petUpdated = "deleted.";
            } else if ($action === "cancelTransaction") {
                $transaction = $pet->transaction;
                Answers::where("pet_id", $pet->id)->where("user_id", $transaction->user_id)->delete();
                ModifyPetEvent::dispatch($pet, $pet->user, $action, $transaction->user);
                $transaction->form->delete();
                $transaction->delete();
                $petUpdated = $pet->update(["pet_status_id" => $newStatusId]);
            } else if ($action === "back") {

                if (!empty($pet->rehome)) {
                    $petUpdated = $pet->update(["pet_status_id" => 3]);
                } else if (!empty($pet->found)) {
                    $petUpdated = $pet->update(["pet_status_id" => 4]);
                } else if (!empty($pet->missing)) {
                    $petUpdated = $pet->update(["pet_status_id" => 5]);
                } else {
                    $petUpdated = $pet->update(["pet_status_id" => 2]);
                }

                ModifyPetEvent::dispatch($pet, $pet->user, $action, null);
            } else {
                ModifyPetEvent::dispatch($pet, $pet->user, $action, null);
                $petUpdated = $pet->update(["pet_status_id" => $newStatusId]);
            }

            if ($action === "approve") {
                $petUpdated = $pet->fresh();
                NewVerifiedPetEvent::dispatch($petUpdated, User::where("id", $petUpdated->user_id)->first());
            }

            $message = match ($action) {
                "approve" => "Pet approved.",
                "cancelTransaction" => "Transaction cancelled.",
                "softDelete" => "Pet soft deleted",
                "delete" => "Pet Deleted.",
                "decline" => "Pet Deleted.",
                "back" => "Pet back.",
                default => throw new InvalidArgumentException('Invalid action'),
            };

            return [
                "data" => $petUpdated,
                "message" => $message
            ];
        });

        return $result;
    }
}
