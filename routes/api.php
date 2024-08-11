<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AdminController;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("register", [UserController::class, "register"]);
Route::post("login", [UserController::class, "login"]);
Route::get("showProductTypes", [UserController::class, "showProductTypes"]);
Route::get("showProducts", [UserController::class, "showProducts"]);
Route::get("showProductsByLikes", [UserController::class, "showProductsByLikes"]);
Route::get("home", [UserController::class, "home"]);
Route::post("search", [UserController::class, "search"]);
Route::get("showCitiesSectors", [UserController::class, "showCitiesSectors"]);
Route::get("showSectors", [UserController::class, "showSectors"]);

Route::get('UsersPhotos/{filename}', function ($filename) {
    $path = base_path('public_html/UsersPhotos/' . $filename);
    if (!File::exists($path)) {
        abort(404, 'File not found');
    }
    return response()->file($path);;
});
Route::get('Ads/{filename}', function ($filename) {
    $path = base_path('public_html/Ads/' . $filename);
    if (!File::exists($path)) {
        abort(404, 'File not found');
    }
    return response()->file($path);;
});
Route::get('products/{filename}', function ($filename) {
    $path = base_path('public_html/products/' . $filename);
    if (!File::exists($path)) {
        abort(404, 'File not found');
    }
    return response()->file($path);;
});

Route::group(["middleware" => ["auth:api"]], function () {
    Route::get("profile", [UserController::class, "profile"]);
    Route::post("logout", [UserController::class, "logout"]);
    Route::post("addProductType", [AdminController::class, "addProductType"])->middleware('checkAdminId');
    Route::post("deleteProductType", [AdminController::class, "deleteProductType"])->middleware('checkAdminId');
    Route::post("editProductType", [AdminController::class, "editProductType"])->middleware('checkAdminId');
    Route::post("addProduct", [AdminController::class, "addProduct"])->middleware('checkEmpId');
    Route::post("shareAd", [AdminController::class, "shareAd"])->middleware('checkEmpId');
    Route::get("deleteAd/{id}", [AdminController::class, "deleteAd"])->middleware('checkEmpId');
    Route::get("toggleLike/{id}", [UserController::class, "toggleLike"])->middleware('checkUserId');
    Route::get("toggleFavourite/{id}", [UserController::class, "toggleFavourite"])->middleware('checkUserId');
    Route::get("showFavourites", [UserController::class, "showFavourites"])->middleware('checkUserId');
    Route::get("toggleBlockProduct/{id}", [AdminController::class, "toggleBlockProduct"])->middleware('checkEmpId');
    Route::get("toggleBlockUser/{id}", [AdminController::class, "toggleBlockUser"])->middleware('checkAdminId');
    Route::get("toggleBlockSector/{id}", [AdminController::class, "toggleBlockSector"])->middleware('checkAdminId');
    Route::post("sendMsg", [UserController::class, "sendMsg"])->middleware('checkUserId');
    Route::get("cancelOrder/{id}", [UserController::class, "cancelOrder"])->middleware('checkUserId');
    Route::post("addSector", [AdminController::class, "addSector"])->middleware('checkAdminId');
    Route::post("addCity", [AdminController::class, "addCity"])->middleware('checkAdminId');
    Route::post("editCity", [AdminController::class, "editCity"])->middleware('checkAdminId');
    Route::post("editSector", [AdminController::class, "editSector"])->middleware('checkAdminId');
    Route::post("addAnswer", [AdminController::class, "addAnswer"])->middleware('checkEmpId');
    Route::get("showMsgs", [AdminController::class, "showMsgs"])->middleware('checkEmpId');
});
