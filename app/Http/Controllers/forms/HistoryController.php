<?php

namespace App\Http\Controllers\forms;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\forms\AllHistoryResource;
use App\Http\Resources\forms\HistoryResource;
use App\Models\forms\History;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $histories = History::with("pet_poster", "pet_interested", "pet.species")->where("pet_poster_id", $user->id)->orWhere("pet_interested_id", $user->id)->get();

        return ApiResponse::success(AllHistoryResource::collection($histories), "Histories", 200);
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
    public function show(Request $request, string $id)
    {
        $history = History::with("pet_interested", "pet_poster", "rehome", "found", "missing", "questions.answers", "pet.species", "pet.breed")->where("public_id", $id)->firstOrFail();

        return ApiResponse::success(new HistoryResource($history), "History.", 200);
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
    public function destroy(string $id)
    {
        //
    }
}
