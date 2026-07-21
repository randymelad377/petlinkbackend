<?php

namespace App\Http\Controllers\forms;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\forms\FormsRequest;
use App\Http\Resources\forms\AllFormResource;
use App\Http\Resources\forms\FormResource;
use App\Models\forms\Form;
use App\Models\forms\Transaction;
use App\Models\pet\Pets;
use App\Models\users\BlockedUsers;
use App\Services\FormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $requestTo = $request->input("requestTo") === "my-request";
        $loadArr = $requestTo ? [
            "forms.pet.species",
            "forms.pet.user",
            "forms" => function ($q) {
                $q->whereDoesntHave('transaction');
            }
        ]
            :
            [
                "pets.species",
                "pets.forms" => function ($q) {
                    $q->whereDoesntHave('transaction');
                }
            ];

        $user = $request->user()->load($loadArr);

        $forms = $requestTo ? $user->forms : $user->pets
            ->flatMap(fn($pet) => $pet->forms)
            ->values();

        return ApiResponse::success(["forms" => AllFormResource::collection($forms)], "forms", 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(FormsRequest $request, FormService $service)
    {
        $fields = $request->validated();
        $result = $service->create($fields, $request->user());
        $pet = Pets::where("public_id", $fields["pet_public_id"])->firstOrFail();

        $userBlocked = BlockedUsers::where(function ($query) use ($request, $pet) {
            $query->where("user_id", $request->user()->id)
                ->where("blocked_user_id", $pet->user_id);
        })->orWhere(function ($query) use ($request, $pet) {
            $query->where("user_id", $pet->user_id)
                ->where("blocked_user_id", $request->user()->id);
        })->exists();

        if ($userBlocked) {
            return ApiResponse::error("You are not permitted to transact with this user because one of you has blocked the other.", 403, null);
        }
        if ($pet->user_id === $request->user()->id) {
            return ApiResponse::error("You are not able to request to you own pet.", 409, null);
        }

        if ($result["data"]) {
            return ApiResponse::success($result["data"], $result["message"], 200);
        }

        return ApiResponse::error($result["message"], 409, null);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $form = Form::with(["user", "pet.user", "user.answers.question", "transaction"])->where("public_id", $id)->first();

        if (!$form) {
            return ApiResponse::error("Form not found", 404, null);
        }

        if ($user->id !== $form->user->id && $user->id !== $form->pet->user->id) {
            return ApiResponse::error("Form not found", 404, null);
        }

        return ApiResponse::success(new FormResource($form), 'Form.', 200);
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
    public function update(Request $request, string $public_id, FormService $service)
    {
        $field = $request->validate([
            "action" => "required|boolean"
        ]);

        $form = Form::where("public_id", $public_id)->first();

        if (!$form) {
            return ApiResponse::success("Form not found.", null, 404);
        }

        $transactionExist = Transaction::where("form_id", $form->id)->exists();

        if ($transactionExist) {
            return ApiResponse::success("Transaction already started", null, 409);
        }

        $pet = Pets::where("id", $form->pet_id)->first();
        $user = $request->user();

        $result = $service->update($field["action"], $form, $pet, $user);

        if ($result["success"]) {
            return ApiResponse::success($result["data"], $result["message"], $result["code"]);
        }

        return ApiResponse::error($result["message"], $result["code"], null);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
