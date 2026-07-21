<?php

namespace App\Http\Controllers\pets;

use App\Events\admin\pets\SendPetEvent;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\forms\AdminAllHistory;
use App\Http\Resources\forms\AdminAllTransactionResource;
use App\Http\Resources\forms\AllHistoryResource;
use App\Http\Resources\forms\AllTransactionResource;
use App\Http\Resources\pets\AllPetResource;
use App\Http\Resources\pets\PetResource;
use App\Models\forms\History;
use App\Models\forms\Transaction;
use App\Models\pet\PetBreed;
use App\Models\pet\Pets;
use App\Models\pet\PetSpecies;
use App\Models\User;
use App\Services\AdminPetService;
use Illuminate\Http\Request;

class AdminPetController extends Controller
{
    public function getPets(Request $request)
    {

        $status = match ($request->input("status")) {
            "pending" => 1,
            "verified" => 2,
            "rehome" => 3,
            "found" => 4,
            "missing" => 5,
            "rehomed" => 6,
            "retrieved" => 7,
            "returned" => 8,
            "inTransaction" => 9,
            "softDeleted" => 10,
            "deleted" => 11,
            default => 1
        };

        $species = PetSpecies::where("species", $request->input("species"))->first();
        $breed = PetBreed::where("breed", $request->input("breed"))->first();
        $gender = strtolower($request->input('gender'));
        $color = strtolower($request->input('color'));

        $pets = null;

        if (!$request->anyFilled(['species', 'breed', 'gender', 'color'])) {
            $pets = Pets::where("pet_status_id", $status)->get();
        } else {

            if ($request->input("species")) {
                $pets = $species ? $species->pets()->where("pet_status_id", $status)->get() : null;
            }

            if ($request->input("breed")) {
                $pets = $breed ? $breed->pets()->where("pet_status_id", $status)->get() : null;
            }

            if ($request->input('gender') || $request->input('color')) {
                $pets = Pets::where("pet_status_id", $status)
                    ->where("gender", $gender)
                    ->get();
            }

            if ($request->input('color')) {
                $pets = Pets::where("pet_status_id", $status)
                    ->where("color", $color)
                    ->get();
            }
        }


        if ($pets && $pets->isNotEmpty()) {
            return ApiResponse::success(AllPetResource::collection($pets), $status, 200);
        }

        return ApiResponse::success(null, $status, 200);
    }

    public function getStatics()
    {
        $pending = Pets::where("pet_status_id", 1)->count();
        $rehome = Pets::where("pet_status_id", 3)->count();
        $found = Pets::where("pet_status_id", 4)->count();
        $missing = Pets::where("pet_status_id", 5)->count();
        $rehomed = History::where("transaction_type_id", 1)->count();
        $returned = History::where("transaction_type_id", 2)->count();
        $retrieved = History::where("transaction_type_id", 3)->count();
        $inTransaction = Pets::where("pet_status_id", 9)->count();
        $deleted = Pets::where("pet_status_id", 10)->count();

        $currentTransaction = Transaction::with("user", "pet.user", "pet.species")->latest()->take(10)->get();
        $successTransaction = History::with("pet.species", "pet_interested", "pet_poster")->latest()->take(10)->get();

        return ApiResponse::success([
            "pets" => [
                "pending" => $pending,
                "rehome" => $rehome,
                "found" => $found,
                "missing" => $missing,
                "rehomed" => $rehomed,
                "returned" => $returned,
                "retrieved" => $retrieved,
                "inTransaction" => $inTransaction,
                "deleted" => $deleted,
            ],
            "currentTransaction" => AdminAllTransactionResource::collection($currentTransaction),
            "successTransaction" => AdminAllHistory::collection($successTransaction)
        ], "Pet statistics", 200);
    }
    public function getPet(Request $request, Pets $pet)
    {
        return ApiResponse::success(new PetResource($pet), "Pet Information", 200);
    }

    public function modifyPet(Request $request, Pets $pet, AdminPetService $service)
    {
        $field = $request->validate([
            "action" => "required|string|in:approve,decline,cancelTransaction,softDelete,delete,back"
        ]);

        $result = $service->modifyPet($field["action"], $pet);

        if ($result["data"]) {
            return ApiResponse::success($result["data"], $result["message"], 200);
        }

        return ApiResponse::error("Something went wrong.", 500, null);
    }

    public function getHistories(Request $request, Pets $pet)
    {
        $histories = $pet->histories()->orderBy("id")->get();

        return ApiResponse::success(AdminAllHistory::collection($histories), "All pet histories", 200);
    }

    public function sendPetNotif(Request $request)
    {
        $fields = $request->validate([
            "pet_id" => "required|string",
            "user_id" => "required|string",
            "message" => "required|string"
        ]);

        $pet = Pets::where("public_id", $fields["pet_id"])->first();
        $user = User::where("public_id", $fields["user_id"])->first();

        if (!$pet || !$user) {
            $message = !$pet ? "Pet not found." : (!$user ? "User not found." : "Not found.");
            return ApiResponse::error($message, 404, null);
        }

        SendPetEvent::dispatch($fields["message"], $user, $pet);

        return ApiResponse::success(true, "Notification sent.", 201);
    }
}
