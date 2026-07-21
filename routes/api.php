<?php

use App\Http\Controllers\ai\ChatController;
use App\Http\Controllers\forms\FormController;
use App\Http\Controllers\forms\HistoryController;
use App\Http\Controllers\forms\TransactionController;
use App\Http\Controllers\messages\ConversationController;
use App\Http\Controllers\messages\MessageController;
use App\Http\Controllers\notification\NotificationController;
use App\Http\Controllers\otherPetInfo\OtherPetInfoController;
use App\Http\Controllers\others\ConcernController;
use App\Http\Controllers\others\ReportController;
use App\Http\Controllers\pets\AdminPetController;
use App\Http\Controllers\pets\PetController;
use App\Http\Controllers\questions\QuestionController;
use App\Http\Controllers\users\AdminUserController;
use App\Http\Controllers\users\BlockedUserController;
use App\Http\Controllers\users\HomeController;
use App\Http\Controllers\users\UserController;
use App\Http\Controllers\VetClinicController;
use App\Models\VetClinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


Route::prefix('user')->group(function () {
    Route::post("register", [UserController::class, "register"]);
    Route::post("login", [UserController::class, "login"]);

    Route::get("getCurrentUser", [UserController::class, "getCurrentUser"]);

    Route::get("getUsers", [UserController::class, "index"]);
    Route::post("send-code", [UserController::class, "sendCode"]);
    Route::put("change-pass", [UserController::class, "changePassword"]);

    Route::middleware("auth:sanctum")->group(function () {
        Route::put("update", [UserController::class, "update"]);
        Route::put("softDelete/{id}", [PetController::class, "softDelete"]);
        Route::get("my-pets", [PetController::class, "myPets"]);
        Route::get("get-user-info-pets/{type}/{public_id}", [UserController::class, "getUser"]);
        Route::put("account-action", [UserController::class, "accountAction"]);
        Route::post("logout", [UserController::class, "logout"]);
        Route::get("have-code", [UserController::class, "isHaveCode"]);
        Route::put("un-display/{id}", [PetController::class, "unDisplay"]);
    });
});

Route::apiResource('pets', PetController::class)
    ->missing(function () {
        return response()->json([
            'message' => 'Pet not found'
        ], 404);
    });


Route::apiResource("message", MessageController::class)->middleware("auth:sanctum");
Route::apiResource("conversation", ConversationController::class)->middleware("auth:sanctum");
Route::apiResource("notifications", NotificationController::class)->middleware("auth:sanctum");
Route::apiResource("concerns", ConcernController::class)->middleware("auth:sanctum");
Route::apiResource("reports", ReportController::class)->middleware("auth:sanctum");
Route::apiResource("block", BlockedUserController::class)->middleware("auth:sanctum");
Route::apiResource("home", HomeController::class);
Route::apiResource("clinic", VetClinicController::class);
Route::apiResource("chat-bot", ChatController::class)->middleware("auth:sanctum");

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('forms', FormController::class)
        ->missing(function () {
            return response()->json([
                'message' => 'Form not found.'
            ], 404);
        });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('transaction', TransactionController::class)
        ->missing(function () {
            return response()->json([
                'message' => 'Form not found.'
            ], 404);
        });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('history', HistoryController::class)
        ->missing(function () {
            return response()->json([
                'message' => 'History not found.'
            ], 404);
        });
});


Route::prefix('transactions')->group(function () {
    Route::middleware("auth:sanctum")->group(function () {
        Route::post("redisplay-pet", [PetController::class, "reDisplayPet"]);
        Route::put("update_questions", [OtherPetInfoController::class, "update_questions"]);
        Route::put("update_other_pet_info", [OtherPetInfoController::class, "update_other_pet_info"]);
    });
});

Route::prefix("admin")->group(function () {
    Route::middleware("auth:sanctum")->group(function () {
        Route::prefix("pets")->group(function () {
            Route::get("get-pets", [AdminPetController::class, "getPets"]);
            Route::get("get-pet/{pet}", [AdminPetController::class, "getPet"]);
            Route::put("modify-pet/{pet}", [AdminPetController::class, "modifyPet"]);
            Route::get("get-histories/{pet}", [AdminPetController::class, "getHistories"]);
            Route::post("send-pet-notif", [AdminPetController::class, "sendPetNotif"]);
            Route::get("get-statistics", [AdminPetController::class, "getStatics"]);
        });
        Route::apiResource("users", AdminUserController::class);
        Route::prefix("user")->group(function () {
            Route::get("get-restriction-users", [AdminUserController::class, "getRestrictionUsers"]);
            Route::put("promote-user", [AdminUserController::class, "promoteUser"]);
            Route::get("get-admins", [AdminUserController::class, "getAdmins"]);
            Route::get("get-user-transactions/{id}", [AdminUserController::class, "getUserTransactions"]);
            Route::get("get-user-histories/{id}", [AdminUserController::class, "getUserHistories"]);
        });
    });
});

Route::get('/db-test', function () {
    return [
        'host' => config('database.connections.mysql.host'),
        'database' => config('database.connections.mysql.database'),
        'ssl' => config('database.connections.mysql.options'),
        'connected' => DB::connection()->getPdo() ? 'yes' : 'no',
    ];
});
