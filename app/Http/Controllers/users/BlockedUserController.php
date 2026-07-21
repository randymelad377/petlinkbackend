<?php

namespace App\Http\Controllers\users;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\users\BlockedUsersResource;
use App\Models\User;
use App\Models\users\BlockedUsers;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Block;

class BlockedUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $blocked_users = $user->blocked_users;

        return ApiResponse::success(BlockedUsersResource::collection($blocked_users), "All blocked users.", 200);
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
        $field = $request->validate([
            "blocked_user_id" => "required|string",
        ]);

        $user = $request->user();

        $blocked_user = User::where('public_id', $field["blocked_user_id"])->first();
        $check_blocked_user = BlockedUsers::where("user_id", $user->id)->where("blocked_user_id", $blocked_user->id)->first();

        if (!$blocked_user || $check_blocked_user) {
            $message = !$blocked_user ? "User not found." : (!empty($check_blocked_user) ? "User blocked already." : "");
            $code = !$blocked_user ? 404 : (!empty($check_blocked_user) ? 403 : 500);
            return ApiResponse::error($message, $code, null);
        }

        $blocked_user = $user->blocked_users()->create(["blocked_user_id" => $blocked_user->id]);

        return ApiResponse::success(true, "Blocked users.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blocked_user = BlockedUsers::where("id", $id)->first();

        if (!$blocked_user) {
            return ApiResponse::error("Blocked user not found", 404, null);
        }

        $blocked_user->delete();

        return ApiResponse::success($blocked_user->blocked_user->public_id, "User unblocked.", 200);
    }
}
