<?php

namespace App\Http\Controllers\messages;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\messages\AllConversation;
use App\Http\Resources\messages\MessagesResource;
use App\Models\messages\Conversation;
use App\Models\messages\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {}

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
        $fields = $request->validate([
            'message' => 'nullable|string|required_without:image',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048|required_without:message',
            'receiver_id' => 'required|string',
        ]);

        $currentUser = $request->user();
        $receiver = User::where("public_id", $fields["receiver_id"])->first();

        if (!$receiver || $receiver->id === $currentUser->id) {
            return ApiResponse::error("User not found.", 404, null);
        }

        $conversation = DB::transaction(function () use ($fields, $currentUser, $receiver, $request) {
            $conversation = Conversation::firstOrCreate([
                "user1_id" => min($currentUser->id, $receiver->id),
                "user2_id" => max($currentUser->id, $receiver->id)
            ]);

            $image = $request->file("image");
            $image_message_path = null;

            if ($image) {
                $image_message_path = $image->store("messages", "public");
            }

            $message = Message::create([
                "sender_id" => $currentUser->id,
                "message" => $fields["message"] ?? null,
                "image_message_path" => $image_message_path,
                "conversation_id" => $conversation->id
            ]);

            return $conversation;
        });

        return ApiResponse::success($conversation->public_id, "Message sent.", 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {

        $currentUser = $request->user();
        $conversation = Conversation::where("public_id", $id)->first();

        if (!$conversation || ($conversation->user1_id !== $currentUser->id && $conversation->user2_id !== $currentUser->id)) {
            return ApiResponse::error("Conversation not found.", 404, null);
        }

        $currentUserMessages = $conversation->messages()->where("sender_id", $currentUser->id)->where("deleted_by_sender", false)->get();
        $receiverMessages = $conversation->messages()->where("sender_id", $currentUser->id === $conversation->user1_id ? $conversation->user2_id : $conversation->user1_id)->where("deleted_by_receiver", false)->get();

        return ApiResponse::success([
            "conversation_id" => $conversation->public_id,
            "receiver" => new AllConversation($conversation),
            "currentUserMessages" => MessagesResource::collection($currentUserMessages),
            "receiverUserMessages" => MessagesResource::collection($receiverMessages),
        ], "All messages.", 200);
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
        $conversation = Conversation::where("public_id", $id)->first();
        $currentUser = $request->user();

        if (!$conversation || ($conversation->user1_id !== $currentUser->id && $conversation->user2_id !== $currentUser->id)) {
            return ApiResponse::error("Conversation not found.", 404, null);
        }

        $currentUserMessages = $conversation->messages()->where("sender_id", $currentUser->id)->where("deleted_by_sender", false);
        $receiverMessages = $conversation->messages()->where("sender_id", $currentUser->id === $conversation->user1_id ? $conversation->user2_id : $conversation->user1_id)->where("deleted_by_receiver", false);

        $result = DB::transaction(function () use ($conversation, $currentUserMessages, $receiverMessages) {

            $currentUserMessages->update([
                'deleted_by_sender' => true,
            ]);

            $receiverMessages->update([
                'deleted_by_receiver' => true,
            ]);

            $conversation->messages()->where("deleted_by_sender", true)->where("deleted_by_receiver", true)->delete();

            return true;
        });

        if ($result) {
            return ApiResponse::success($conversation, "Conversation deleted.", 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id) {}
}
