<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AdminController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("register", [UserController::class, "register"]);
Route::post("login", [UserController::class, "login"]);
Route::get("showProductTypes", [UserController::class, "showProductTypes"]);

Route::group(["middleware" => ["auth:api"]], function () {
    Route::post("logout", [UserController::class, "logout"]);
    Route::post("addProductType", [AdminController::class, "addProductType"])->middleware('checkAdminId');
    Route::post("deleteProductType", [AdminController::class, "deleteProductType"])->middleware('checkAdminId');
    Route::post("editProductType", [AdminController::class, "editProductType"])->middleware('checkAdminId');
    Route::post("addProduct", [AdminController::class, "addProduct"])->middleware('checkEmpId');
});