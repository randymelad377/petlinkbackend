<?php

namespace App\Http\Controllers\ai;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllAiChatResource;
use App\Models\ChatBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return ApiResponse::success(AllAiChatResource::collection($user->ai_messages), "Last conversation with ai", 200);
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

        $message = $request->validate(["message" => "required|string"]);

        $result = DB::transaction(function () use ($message, $user, $request) {
            $userMessage = $user->ai_messages()->create(["message" => $message["message"], "isAi" => false]);

            $response = Http::withToken(config('services.groq.key'))
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $request->message . "if question is not related about pet, reply with i am programmed to answer only pets care related., (response must not greater than 200 characters)"
                        ]
                    ]
                ]);

            $result = $response->json()['choices'][0]['message']['content'];
            if ($result)
                $user->ai_messages()->create(["message" => $result, "isAi" => true]);

            return $result;
        });

        return ApiResponse::success(AllAiChatResource::collection($user->ai_messages), "Ai Response.", 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $user->ai_messages()->delete();
        return ApiResponse::success(true, "Ai chats deleted", 200);
    }
}
