<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SkillController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");

Route::get("/organizations", [OrganizationController::class, "index"])->name(
    "api.organizations.index"
);
Route::get("/organizations/{organization:slug}", [
    OrganizationController::class,
    "show",
])->name("api.organizations.show");

Route::get("/posts", [PostController::class, "index"])->name("api.posts.index");
Route::get("/posts/{post}", [PostController::class, "show"])->name(
    "api.posts.show"
);

Route::get("/skills", [SkillController::class, "index"])->name(
    "api.skills.index"
);
Route::get("/skills/{skill}", [SkillController::class, "show"])->name(
    "api.skills.show"
);
Route::get("/posts/{post:slug}", [PostController::class, "show"])->name(
    "api.posts.show"
);
