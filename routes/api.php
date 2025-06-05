<?php

use App\Http\Controllers\Api\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");

Route::get("/organizations", [OrganizationController::class, "index"]);
Route::get("/organizations/{organization:slug}", [
    OrganizationController::class,
    "show",
]);
