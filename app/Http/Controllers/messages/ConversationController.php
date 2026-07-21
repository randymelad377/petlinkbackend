<?php

namespace App\Http\Controllers\messages;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\messages\AllConversation;
use App\Models\messages\Conversation;
use App\Models\messages\Message;
use App\Models\User;
use App\Models\users\BlockedUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $name = $request->query("name");
        $parts = explode(' ', $name);

        $user = $request->user();

        $blockedUserIds = $user->blocked_users()->pluck('blocked_user_id')->toArray();
        $blockedByUserIds = BlockedUsers::where("blocked_user_id", $user->id)->pluck("user_id")->toArray();
        $excludedUserIds = array_merge($blockedUserIds, $blockedByUserIds);

        $conversaitionsQuery = Conversation::where(function ($query) use ($user) {
            $query->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })
            ->where(function ($query) use ($excludedUserIds, $user) {
                $query->whereNotIn('user1_id', $excludedUserIds)
                    ->whereNotIn('user2_id', $excludedUserIds);
            });

        if (!empty($name)) {

            $firstName = strtolower(trim($parts[0]));
            $lastName = strtolower(trim($parts[1] ?? ''));

            $searchUserIds = User::where(function ($query) use ($firstName, $lastName) {
                $query->whereRaw('LOWER(firstName) = ?', [$firstName])
                    ->whereRaw('LOWER(lastName) = ?', [$lastName]);
            })
                ->orWhere(function ($query) use ($firstName, $lastName) {
                    $query->whereRaw('LOWER(firstName) = ?', [$lastName])
                        ->whereRaw('LOWER(lastName) = ?', [$firstName]);
                })
                ->pluck('id');

            $conversaitionsQuery->where(function ($query) use ($searchUserIds, $user) {
                $query->where(function ($q) use ($searchUserIds, $user) {
                    $q->where('user1_id', $user->id)
                        ->whereIn('user2_id', $searchUserIds);
                })
                    ->orWhere(function ($q) use ($searchUserIds, $user) {
                        $q->where('user2_id', $user->id)
                            ->whereIn('user1_id', $searchUserIds);
                    });
            });
        }

        $conversations = $conversaitionsQuery->get();

        return ApiResponse::success(AllConversation::collection($conversations), "All conversations.", 200);
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
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {

        $conversation = Conversation::where("public_id", $id)->first();

        $currentUser = $request->user();
        $conversation = Conversation::where("public_id", $id)->first();

        if (!$conversation || ($conversation->user1_id !== $currentUser->id && $conversation->user2_id !== $currentUser->id)) {
            ApiResponse::error("Conversation not found.", 404, null);
        }

        return ApiResponse::success(new AllConversation($conversation), "Conversation", 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $conversation = Conversation::where("public_id", $id)->first();

        return DB::transaction(function () use ($conversation, $user) {

            if ($conversation->user1_id == $user->id) {
                $conversation->user1_deleted = true;
            } elseif ($conversation->user2_id == $user->id) {
                $conversation->user2_deleted = true;
            }

            if ($conversation->user1_deleted && $conversation->user2_deleted) {
                Message::where("conversation_id", $conversation->id)->delete();
                $conversation->delete();
            } else {
                $conversation->save();
            }

            return ApiResponse::success(null, "Conversation deleted.", 200);
        });
    }
}
