<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {;

    $user =  $request->user();
    $user['profile'] = $user->profile;
    return $user;
})->middleware('auth:sanctum');


Route::post("signUp", [UserController::class, "signUp"]);
Route::post("file", [StorageController::class, "store"]);
Route::post("code", [UserController::class, "verifyCode"]);
Route::post("signIn", [UserController::class, 'signIn']);
Route::post("forgot", [UserController::class, "forgot"]);
Route::post("reset", [UserController::class, "reset"]);
Route::post("status", [OrderStatusController::class, "store"]);
Route::get("status", [OrderStatusController::class, "index"]);
Route::get("serach", [ProductController::class, 'serach']);
Route::apiResource("product", ProductController::class);
Route::apiResource("category", CategoryController::class);
Route::get("category/{id}/product", [CategoryController::class, "getProduct"]);

// Route::get("profileUser", [UserController::class, "show"])->middleware('auth:sanctum');

// Route::apiResource("profile", ProfileController::class)->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put("profile", [ProfileController::class, "update"]);
    Route::post("profile", [ProfileController::class, "store"]);
});






Route::middleware(['auth:sanctum'])->group(function () {
    Route::post("address", [AddressController::class, "store"]);
    Route::get("address", [AddressController::class, "show"]);
    Route::get("address/{id}/activate", [AddressController::class, "activate"]);
    Route::put("address/{id}", [AddressController::class, "update"]);
    Route::delete("address/{id}", [AddressController::class, "destroy"]);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::post("order", [OrderController::class, 'addOrder']);
    Route::get("order-show/{id}", [OrderController::class, 'showByID']);
    Route::get("order/user", [OrderController::class, 'show']);
    Route::get("order/user/{id}", [OrderController::class, 'showWithStats']);
    Route::put("order/{id}", [OrderController::class, 'update']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/orders/{id}/accept', [OrderController::class, 'acceptOrder']);
    Route::post('/orders/{id}/reject', [OrderController::class, 'rejectOrder']);
    Route::post('/orders/{id}/ship', [OrderController::class, 'shipOrder']);
    Route::post('/orders/{id}/deliver', [OrderController::class, 'deliverOrder']);
    Route::post('/orders/{id}/return', [OrderController::class, 'returnOrder']);
    Route::post('/orders/{id}/delete', [OrderController::class, 'deleteOrder']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
});




Route::get("order", [OrderController::class, 'index']);



// psotman 
