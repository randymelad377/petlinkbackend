<?php

namespace App\Http\Controllers\forms;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\forms\AllTransactionResource;
use App\Http\Resources\forms\TransactionResource;
use App\Models\forms\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transactionMyRequest = $user->transactions()->with("transaction.pet.user", "transaction.pet.species")->get();
        $transactionRequestToMyPets = $user->pets()
            ->with("transaction.user", "transaction.pet.species")
            ->get()
            ->pluck('transaction')
            ->filter();

        return ApiResponse::success(AllTransactionResource::collection($transactionMyRequest->merge($transactionRequestToMyPets)), "Transactions.", 200);
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
        $user = $request->user();

        $transaction = Transaction::with('pet.user', "user", "pet.species", "pet.breed", "pet.images", "form")
            ->where('public_id', $id)
            ->first();

        if (!$transaction) {
            return ApiResponse::error("Transaction not found.", 404, null);
        }

        if ($user->id != $transaction->user_id && $user->id != $transaction->pet->user->id) {
            return ApiResponse::error("Transaction not found.", 404, null);
        }

        return ApiResponse::success(new TransactionResource($transaction), "Transaction", 200);
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
    public function update(Request $request, string $public_id, TransactionService $service)
    {
        $field = $request->validate([
            "action" => "required|boolean"
        ]);
        $user = $request->user();
        $transaction = Transaction::where("public_id", $public_id)->first();

        if (!$transaction) {
            return ApiResponse::error("Transaction not found", 404, null);
        }

        $result = $service->update($field["action"], $transaction, $user);

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
