<?php

namespace App\Http\Controllers\users;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\pets\AllPetResource;
use App\Models\pet\Pets;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $user = null;
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }
        }

        $query = Pets::with("images");

        if ($user) {
            $query = $query->where("user_id", "!=", $user->id);
        }

        $rehomePet = (clone $query)->where('pet_status_id',  3)->orderByDesc('id')->limit(5)->get();
        $foundPet = (clone $query)->where('pet_status_id',  4)->orderByDesc('id')->limit(5)->get();
        $missingPet = (clone $query)->where('pet_status_id',  5)->orderByDesc('id')->limit(5)->get();

        return ApiResponse::success([
            "rehome" => AllPetResource::collection($rehomePet),
            "found" => AllPetResource::collection($foundPet),
            "missing" => AllPetResource::collection($missingPet)
        ], "Pet for rehome", 200);
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
