<?php

namespace App\Http\Controllers\users;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\users\StoreUserRequest;
use App\Http\Requests\users\UpdateUserRequest;
use App\Http\Resources\users\AllUserResource;
use App\Http\Resources\users\UserInfoPetsResource;
use App\Http\Resources\users\UserResource;
use App\Models\pet\Pets;
use App\Models\User;
use App\Models\users\BlockedUsers;
use App\Notifications\ChangePasswordCode;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $token = $request->bearerToken();

        $user = null;
        $excludedUserIds = null;
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
                $blockedUserIds = $user->blocked_users()->pluck('blocked_user_id')->toArray();
                $userBlockedBy = BlockedUsers::where("blocked_user_id", $user->id)->pluck('user_id')->toArray();
                $excludedUserIds = array_merge($blockedUserIds, $userBlockedBy);
            }
        }

        $users = null;
        $search = $request->input("name");
        $query = User::where("user_role_id", 1);

        if ($user) {
            $query->where("id", "!=", $user->id)->whereNotIn("id", $excludedUserIds);
        }

        if ($search) {
            $parts = explode(' ', $search);
            $firstPart = strtolower($parts[0]);
            $secondPart = strtolower($parts[1] ?? "");

            if (!empty($secondPart)) {
                $users = $query->whereRaw('LOWER(firstName) = ?', [$firstPart])
                    ->whereRaw('LOWER(lastName) = ?', [$secondPart])
                    ->whereNotIn("id", $excludedUserIds)
                    ->get();
                if ($users->isEmpty()) {
                    $users = User::where("user_role_id", 1)
                        ->when($user, fn($q) => $q->where('id', '!=', $user->id))
                        ->whereRaw('LOWER(firstName) = ?', [$firstPart])
                        ->whereRaw('LOWER(lastName) = ?', [$secondPart])
                        ->whereNotIn("id", $excludedUserIds)
                        ->get();
                }
            } else {
                $users = $query
                    ->where(function ($q) use ($firstPart) {
                        $q->whereRaw('LOWER(firstName) = ?', [$firstPart])
                            ->orWhereRaw('LOWER(lastName) = ?', [$firstPart]);
                    })
                    ->whereNotIn("id", $excludedUserIds)
                    ->get();
            }
        } else {
            $users = $query->get();
        }


        return ApiResponse::success(
            AllUserResource::collection($users),
            "Users.",
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function register(StoreUserRequest $request, UserService $service)
    {
        $fields = $request->validated();
        $image = $request->file("image");
        $fields['password'] = Hash::make($fields['password']);
        $user = $service->register($fields, $image);

        return ApiResponse::success($user, "Registered.", 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            "username" => "required|string|max:255",
            "password" => "required",
        ]);

        $user = User::where("username", $fields["username"])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $token = $user->createToken($user->email)->plainTextToken;

        if ($user && $user->user_status_id !== 1) {

            if (Carbon::parse($user->suspend_at)->addDays(30)->isPast()) {
                if ($user->suspend_at->isPast()) {
                    $user->update([
                        "user_status_id" => 1,
                    ]);
                }
            }

            $endDate = Carbon::parse($user->suspend_at)->addDays(30);
            $daysLeft = (int) max(0, now()->diffInDays($endDate, false));


            $message = $user->user_status_id === 2 ? "This account is suspended for {$daysLeft} more days."
                : "This account is banned.";

            return ApiResponse::error($message, 403, null);
        }

        return response()->json([
            'status' => true,
            'message' => 'User login successfully',
            'token' => $token,
            "data" => [
                "user_id" => $user->id,
                "user_role" => $user->role->role
            ]
        ], 200);
    }
    /**
     * Display the specified resource.
     */
    public function getCurrentUser(Request $request)
    {
        $token = $request->bearerToken();

        $user = null;

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }
        }

        return ApiResponse::success($user ? new UserResource($user) : null, $user ? "User Information." : "No user found.", 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, UserService $service)
    {
        $fields = $request->validated();
        $user = $request->user();
        $image = $request->file("image");

        $result = $service->update($fields, $user, $image);

        if ($result["changed"]) {
            return ApiResponse::success($result["user"], "Personal Information Updated.", 200);
        }

        return ApiResponse::error("No changes detected.", 409, $result["user"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return "destroy";
    }

    public function getUser(string $type, string $public_id)
    {

        $petType = match ($type) {
            "rehome" => 1,
            "found" => 2,
            "missing" => 3,
            default => 1
        };

        $typeQuery = match ($type) {
            "rehome" => "pets.rehome",
            "found" => "pets.found",
            "missing" => "pets.missing",
            default => "pets.rehome"
        };

        $user = User::with([
            "pets" => function ($query) use ($petType) {
                $query->select(
                    "id",
                    "user_id",
                    "transaction_type_id",
                    "pet_status_id",
                    "pet_breed_id",
                    "pet_species_id",
                    "public_id",
                    "created_at"
                )->where("transaction_type_id", $petType);
            },
            "pets.images",
            "pets.species",
            $typeQuery
        ])
            ->where("public_id", $public_id)
            ->first();

        if ($user) {
            return ApiResponse::success(new UserInfoPetsResource($user), "User information.", 200);
        }

        return ApiResponse::error("User not found", 404, null);
    }

    public function accountAction(Request $request)
    {

        $fields = $request->validate([
            "user_id" => "required|string",
            "action" => "required|string"
        ]);

        $user = User::where("public_id", $fields["user_id"])->first();

        if (!$user) {
            return ApiResponse::error("User not found.", 404, null);
        }

        $actionId = match ($fields["action"]) {
            "re-activate" => 1,
            "suspend" => 2,
            "ban" => 3,
            default => 0
        };

        $message = match ($actionId) {
            1 => "User is re-activated.",
            2 => "User is suspended. (30 days)",
            3 => "User banned.",
            default => "Not valid Action"
        };

        $user->update(["user_status_id" => $actionId, "suspend_at" => now()]);
        if ($actionId === 2) $user->update(["warningCount" => $user->warningCount + 1]);

        return ApiResponse::success(new UserResource($user->fresh()), $message, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User logout successfully'
        ], 200);
    }

    public function sendCode(Request $request)
    {
        $email = $request->validate(["email" => "required|email"]);

        $token = $request->bearerToken();

        $user = null;
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }
        }

        $code = (string) random_int(100000, 999999);

        if ($user && $email["email"] !== $user->email) {
            return ApiResponse::error("Email not match.", 403, null);
        }

        if (!$user) {
            $user = User::where("email", $email)->first();
            if (!$user) {
                return ApiResponse::error("User not found.", 404, null);
            }
        }

        if ((bool) cache()->get("otp_{$user->email}")) {
            return ApiResponse::success(true, "Verification code sent. (Do not refresh this page)", 200);
        }
        cache()->put("otp_{$user->email}", $code, now()->addMinutes(10));
        $user->notify(new ChangePasswordCode(($code)));

        return ApiResponse::success(true, "Verification code sent. (Do not refresh this page)", 200);
    }

    public function isHaveCode(Request $request)
    {
        $user = $request->user();
        $haveCode = (bool) cache()->get("otp_{$user->email}");
        return ApiResponse::success($haveCode, "Have code", 200);
    }
    public function changePassword(Request $request)
    {
        $token = $request->bearerToken();

        $user = null;
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }
        }

        $validation = !$user ? [
            "email" => "required|email",
            "password" => "required|confirmed",
            "code" => "required|string"
        ] : [
            "password" => "required|confirmed",
            "code" => "required|string"
        ];

        $fields = $request->validate($validation);

        if (!$user) {
            $user = User::where("email", $fields["email"])->first();
        }

        $savedCode = cache()->get("otp_{$user->email}");

        if ($savedCode !== $fields["code"]) {
            return ApiResponse::error("Code not mnatch.", 409, null);
        }

        $user->update([
            "password" => Hash::make($fields["password"])
        ]);

        cache()->forget("otp_{$user->email}");

        return ApiResponse::success(true, "Password Changed.", 200);
    }
}
