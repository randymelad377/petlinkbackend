<?php

namespace App\Http\Controllers\notification;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\notifications\AllNotificationsResource;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $user = $request->user();
        $userNotifications = $user->notifications;

        if ($user->user_role_id === 3 || $user->user_role_id === 2) {
            $adminUser = User::where("user_role_id", 3)->first();
            $userNotifications = $adminUser->notifications;
        }


        return ApiResponse::success(AllNotificationsResource::collection($userNotifications), "Notifications.", 200);
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
        //
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
    public function update(Request $request, string $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->find($id);

        if ($user->user_role_id !== 1) {
            $user = User::where("user_role_id", 3)->first();
            $notification = User::where("user_role_id", 3)->first()->notifications()->find($id);
        }

        if (!$notification) {
            return ApiResponse::error("Notification not found.", 404);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        } else {
            $notification->update([
                'read_at' => null,
            ]);
        }

        return ApiResponse::success(
            AllNotificationsResource::collection($user->notifications),
            "Notification updated.",
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->find($id);

        if ($user->user_role_id !== 1) {
            $user = User::where("user_role_id", 3)->first();
            $notification = User::where("user_role_id", 3)->first()->notifications()->find($id);
        }

        $notification->delete();

        return ApiResponse::success(AllNotificationsResource::collection($user->notifications), "Notification deleted.", 200);
    }
}
