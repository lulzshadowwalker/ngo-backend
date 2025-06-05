<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VolunteeringInterestController;

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

Route::get("/skills", [SkillController::class, "index"])->name(
    "api.skills.index"
);

Route::get("/skills/{skill}", [SkillController::class, "show"])->name(
    "api.skills.show"
);

Route::get("/locations", [LocationController::class, "index"])->name(
    "api.locations.index"
);

Route::get("/locations/{location}", [LocationController::class, "show"])->name(
    "api.locations.show"
);

Route::get("/volunteering-interests", [
    VolunteeringInterestController::class,
    "index",
])->name("api.volunteering-interests.index");

Route::get("/volunteering-interests/{volunteeringInterest}", [
    VolunteeringInterestController::class,
    "show",
])->name("api.volunteering-interests.show");

Route::get("/skills", [SkillController::class, "index"])->name(
    "api.skills.index"
);

Route::get("/skills/{skill}", [SkillController::class, "show"])->name(
    "api.skills.show"
);

Route::get("/posts/{post:slug}", [PostController::class, "show"])->name(
    "api.posts.show"
);
