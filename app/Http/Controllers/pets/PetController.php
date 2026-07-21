<?php

namespace App\Http\Controllers\pets;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\pets\ReDisplayPetRequest;
use App\Http\Requests\pets\StorePetsRequest;
use App\Http\Requests\pets\UpdatePetsRequest;
use App\Http\Resources\pets\AllPetResource;
use App\Http\Resources\pets\PetResource;
use App\Http\Resources\pets\VerifiedPetResource;
use App\Models\forms\Form;
use App\Models\pet\PetBreed;
use App\Models\pet\Pets;
use App\Models\pet\PetSpecies;
use App\Services\PetService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class PetController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [new Middleware("auth:sanctum", except: ["index"])];
    }

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

        $status = match ($request->input("status")) {
            "rehome" => 3,
            "found" => 4,
            "missing" => 5,
            "rehomed" => 3,
            "returned" => 4,
            "retrieved" => 5,
            default => 3
        };

        $species = PetSpecies::where("species", $request->input("species"))->first();
        $breed = PetBreed::where("breed", $request->input("breed"))->first();
        $gender = strtolower($request->input('gender'));
        $color = strtolower($request->input('color'));

        $pets = null;

        if (!$request->anyFilled(['species', 'breed', 'gender', 'color'])) {
            $query = Pets::where("pet_status_id", $status);

            if ($user) {
                $query->where("user_id", "!=", $user->id);
            }
            $pets = $query->get();
        } else {

            if ($request->input("species")) {
                $pets = $species ? $species->pets()->where("pet_status_id", $status)->where("user_id", "!=", $user ? $user->id : 0)->get() : null;
            }

            if ($request->input("breed")) {
                $pets = $breed ? $breed->pets()->where("pet_status_id", $status)->where("user_id", "!=", $user ? $user->id : 0)->get() : null;
            }

            if ($request->input('gender') || $request->input('color')) {
                $pets = Pets::where("pet_status_id", $status)
                    ->where("gender", $gender)
                    ->where("user_id", "!=", $user ? $user->id : 0)
                    ->get();
            }

            if ($request->input('color')) {
                $pets = Pets::where("pet_status_id", $status)
                    ->where("color", $color)
                    ->where("user_id", "!=", $user ? $user->id : 0)
                    ->get();
            }
        }

        if ($pets && $pets->isNotEmpty()) {
            return ApiResponse::success(AllPetResource::collection($pets), $status, 200);
        }

        return ApiResponse::success(null, $status, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(StorePetsRequest $request) {}

    /** 
     * Store a newly created resource in storage.
     */
    public function store(StorePetsRequest $request, PetService $service)
    {
        $fields = $request->validated();
        $images = $request->file('images');
        $user = $request->user();

        $pet = $service->store($fields, $images, $user);

        if ($pet) {
            return ApiResponse::success(new PetResource($pet), "Pet created.", 201);
        }

        return ApiResponse::error("Something went wrong.", 500, null);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pets $pet)
    {
        $data = $pet->transaction_type_id === 4 ? new VerifiedPetResource($pet) : new PetResource($pet);

        return ApiResponse::success($data, "Pet information", 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return "edit";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePetsRequest $request, Pets $pet, PetService $service)
    {
        $fields = $request->validated();
        $images = $request->file("images");

        if ($pet->user_id !== $request->user()->id) {
            return ApiResponse::error("Forbidden.", 403, null);
        }

        if ($pet->pet_status_id !== 1 || $pet->pet_status_id !== 2) {
            return ApiResponse::error("You are only able to edit you pet when pending or not available for other users.");
        }
        $result = $service->update($fields, $images, $pet);

        if ($result["changed"]) {
            return ApiResponse::success($result["data"], $result["message"], 200);
        }

        return ApiResponse::error($result["message"], 409, $result["data"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return "destroy";
    }

    public function softDelete(Request $request, $id)
    {
        $pet = Pets::where("public_id", $id)->first();

        if (!$pet) {
            ApiResponse::error("Pet not found.", 404, null);
        }
        if ($request->user()->id !== $pet->user_id) {
            ApiResponse::error("You are not allowed to delete this pet.", 403, null);
        }

        if ($pet->transaction()->exists()) {
            return ApiResponse::error("Please cancel or finish transaction first.", 403, null);
        }

        $pet->update(["pet_status_id" => 10]);

        return ApiResponse::success("deleted.", "Pet deleted", 200);
    }

    public function myPets(Request $request)
    {
        $status = match ($request->input("status")) {
            "pending" => 1,
            "verified" => 2,
            "rehome" => 3,
            "found" => 4,
            "missing" => 5,
            "inTransaction" => 9,
            default => 1
        };

        $user = $request->user();
        $pets = $user->pets()->where("pet_status_id", $status)->get();

        return ApiResponse::success(AllPetResource::collection($pets), "All user pets.", 200);
    }

    public function reDisplayPet(ReDisplayPetRequest $request, PetService $service)
    {
        $fields = $request->validated();
        $user = $request->user();

        $pet = Pets::where("public_id", $fields["pet_public_id"])->firstOrFail();

        $result = $service->reDisplayPet($fields, $pet);

        if ($result["data"]) {
            return ApiResponse::success($result["data"], $result["message"], 200);
        }

        return ApiResponse::error("Something went wrong", 500, null);
    }
    public function unDisplay(Request $request, string $id)
    {
        $user = $request->user();

        $pet = $user->pets()->where("public_id", $id)->first();

        if (!$pet) {
            return ApiResponse::error("Pet not found.", 404, null);
        }

        if ($pet->transaction()->exists()) {
            return ApiResponse::error("Please cancel or finish transaction first.", 403, null);
        }

        if (!in_array($pet->pet_status_id, [1, 2, 3])) {
            return ApiResponse::error("Pet is not displayed to other users.", 403, null);
        }

        $result = DB::transaction(function () use ($pet) {
            if ($pet->pet_status_id === 3) {
                $pet->rehome()->delete();
            } else if ($pet->pet_status_id === 4) {
                $pet->found()->delete();
            } else {
                $pet->missing()->delete();
            }

            $pet->questions()->delete();
            Form::where("pet_id", $pet->id)->delete();

            $pet->update(["pet_status_id", 2]);

            return $pet->refresh();
        });

        if ($result) {
            return ApiResponse::success($request, "Pet is not display anymore.", 200);
        }
    }
}
