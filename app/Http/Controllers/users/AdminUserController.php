<?php

namespace App\Http\Controllers\users;

use App\Events\admin\users\AdminSendEvent;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\forms\AdminAllHistory;
use App\Http\Resources\forms\AdminAllTransactionResource;
use App\Http\Resources\forms\AdminUserHistoryResource;
use App\Http\Resources\forms\AdminUserTransactionResource;
use App\Http\Resources\pets\AllPetResource;
use App\Http\Resources\users\AllUserResource;
use App\Http\Resources\users\UserInfoPetsResource;
use App\Http\Resources\users\UserResource;
use App\Models\forms\History;
use App\Models\forms\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = null;

        $status = match ($request->query("status")) {
            "active" => 1,
            "suspend" => 2,
            "banned" => 3,
            default => 1
        };

        $search = $request->query("name");
        $query = User::where("user_role_id", 1)->where("user_status_id", $status);

        if ($search) {
            $parts = explode(' ', $search);
            $firstPart = strtolower($parts[0]);
            $secondPart = strtolower($parts[1] ?? "");

            if (!empty($secondPart)) {
                $users = $query->whereRaw('LOWER(firstName) = ?', [$firstPart])
                    ->whereRaw('LOWER(lastName) = ?', [$secondPart])
                    ->get();
                if ($users->isEmpty()) {
                    $users =  $query->where("user_role_id", 1)
                        ->whereRaw('LOWER(firstName) = ?', [$firstPart])
                        ->whereRaw('LOWER(lastName) = ?', [$secondPart])
                        ->get();
                }
            } else {
                $users =  $query->where(function ($q) use ($firstPart) {
                    $q->whereRaw('LOWER(firstName) = ?', [$firstPart])
                        ->orWhereRaw('LOWER(lastName) = ?', [$firstPart]);
                })
                    ->get();
            }
        } else {
            $users = $query->get();
        }

        return ApiResponse::success(AllUserResource::collection($users), "All users.", 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->user_role_id === 1) {
            return ApiResponse::error("Forbidden.", 403, null);
        }

        $fields = $request->validate([
            "user_id" => "required|string",
            "message" => "required|string"
        ]);

        $receiver = User::where("public_id", $fields["user_id"])->first();

        if (!$receiver) {
            return ApiResponse::error("User not found.", 404, null);
        }

        AdminSendEvent::dispatch($receiver, $fields["message"]);

        return ApiResponse::success(true, "Notification sent.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = User::with("pets")->where("public_id", $id)->first();

        $petType = match ($request->query("status")) {
            "pending" => 1,
            "verified" => 2,
            "rehome" => 3,
            "found" => 4,
            "missing" => 5,
            "inTransaction" => 9,
            "softDeleted" => 10,
            default => 1
        };

        if (!$user) {
            return ApiResponse::error("User not found.", 404, null);
        }

        $data = [
            "user_info" => new UserResource($user),
            "user_pets" => AllPetResource::collection($user->pets()->where("pet_status_id", $petType)->get())
        ];

        return ApiResponse::success($data, "User info and pets", 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function getRestrictionUsers(Request $request)
    {
        $param = $request->query("restriction_type");

        $restriction = match ($param) {
            "suspended" => 2,
            "banned" => 3,
            default => 2
        };

        $users = User::where("user_status_id", $restriction)->get();

        return ApiResponse::success(AllUserResource::collection($users), "All users {$request->query("restriction_type")}", 200);
    }

    public function promoteUser(Request $request)
    {
        $user = $request->user();

        if ($user->user_role_id !== 3) {
            return ApiResponse::error("Forbidden", 403, null);
        }

        $fields = $request->validate(["user_id" => "string|required", "action" => "boolean|required"]);
        $selectedUser = User::where("public_id", $fields["user_id"])->first();
        $action = $fields["action"];

        if (!$selectedUser) {
            return ApiResponse::error("User not found.", 404, null);
        }

        if ($user->pets->isNotEmpty() || $user->transactions->isNotEmpty() || $user->forms->isNotEmpty()) {
            return ApiResponse::error("Account must be new.", 403, null);
        }

        $selectedUser->update(["user_role_id" => $action ? 2 : 1]);

        return ApiResponse::success(new UserResource($selectedUser->fresh()), $action ? "User promoted to admin" : "User demoted to user ", 201);
    }

    public function getAdmins(Request $request)
    {
        $admins = User::where("user_role_id", 2)->get();
        return ApiResponse::success(AllUserResource::collection($admins), "All admins.", 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getUserTransactions(Request $request, string $id)
    {
        $user = User::where("public_id", $id)->first();

        if (!$user) {
            return ApiResponse::error("User not found", 404, null);
        }

        $userInterestedTransaction = $user->transactions()->with("pet.user", "pet.species")->get();
        $userOwnerTransaction = Transaction::with("user", "pet.user", "pet.species")->whereIn('pet_id', $user->pets()->pluck('id'))->get();

        $transactions = $userInterestedTransaction->merge($userOwnerTransaction);
        return ApiResponse::success(AdminUserTransactionResource::collection($transactions), "User transactions", 200);
    }

    public function getUserHistories(Request $request, string $id)
    {
        $user = User::where("public_id", $id)->first();

        if (!$user) {
            return ApiResponse::error("User not found", 404, null);
        }

        $histories = History::where("pet_poster_id", $user->id)->orWhere("pet_interested_id", $user->id)->get();

        return ApiResponse::success(AdminUserHistoryResource::collection($histories), "User transactions", 200);
    }
}
