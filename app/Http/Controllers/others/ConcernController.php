<?php

namespace App\Http\Controllers\others;

use App\Events\others\ConcernEvent;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\others\AllConcernResource;
use App\Models\others\Concern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConcernController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->user_role_id === 1) {
            ApiResponse::error("Forbidden.", 403, null);
        }

        $concerns = Concern::with('user')->get();

        return ApiResponse::success(AllConcernResource::collection($concerns), "All Concerns", 200);
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
        $fields = $request->validate([
            'message' => 'nullable|string|min:3|required_without:image',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048|required_without:message',
        ]);

        $user = $request->user();

        //2 DAYS BEFORE SEND NEW CONCERN
        $lastConcern = $user->concerns()->latest()->first();
        if ($lastConcern && $lastConcern->created_at->diffInDays(now()) < 1) {
            return ApiResponse::error("Please wait after 2 days before sending again.", 403, null);
        }

        $result = DB::transaction(function () use ($fields, $user, $request) {
            $image_path = null;

            if ($request->hasFile('image')) {
                $image_path = $request->file('image')->store('concerns', 'public');
            }

            $concern = $user->concerns()->create(["message" => $fields["message"], "image_path" => $image_path, "isRead" => false]);

            return $concern;
        });

        return ApiResponse::success($result, "Feedback sent to admin group.", 200);
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
        $concern = Concern::where("id", $id)->first();
        $isRead = $concern->isRead;

        $concern->update(["isRead" => !$isRead]);

        return ApiResponse::success(AllConcernResource::collection(Concern::with("user")->get()), $isRead ? "Mark as read" : "Mark as unread", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $concern = Concern::where('id', $id)->first();

        if (!$concern) {
            return ApiResponse::error("Feedback not found", 404, null);
        }
        $concern->delete();
        return ApiResponse::success(AllConcernResource::collection(Concern::with("user")->get()), "Concern Deleted", 200);
    }
}
