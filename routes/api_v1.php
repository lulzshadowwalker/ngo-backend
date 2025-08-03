<?php

use App\Http\Controllers\Api\LikePostController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\UserPreferencesController;
use App\Http\Controllers\Api\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/me/preferences', [UserPreferencesController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.profile.preferences.index');
Route::patch('/me/preferences', [UserPreferencesController::class, 'update'])->middleware('auth:sanctum')->name('api.v1.profile.preferences.update');
// Route::get('/me', [ProfileController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.profile.index');

Route::get('/organizations', [OrganizationController::class, 'index'])->name('api.v1.organizations.index');
Route::get('/organizations/{organization:slug}', [OrganizationController::class, 'show',])->name('api.v1.organizations.show');

Route::get('/skills', [SkillController::class, 'index'])->name('api.v1.skills.index');
Route::get('/skills/{skill}', [SkillController::class, 'show'])->name('api.v1.skills.show');

Route::get('/locations', [LocationController::class, 'index'])->name('api.v1.locations.index');
Route::get('/locations/{location}', [LocationController::class, 'show'])->name('api.v1.locations.show');

Route::get('/posts', [PostController::class, 'index'])->name('api.v1.posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('api.v1.posts.show');

Route::post('/posts/{post:slug}/like', [LikePostController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.posts.likes.store');
Route::delete('/posts/{post:slug}/like', [LikePostController::class, 'destroy'])->middleware('auth:sanctum')->name('api.v1.posts.likes.destroy');

// Route::post('/posts/{post:slug}/comments', [CommentPostController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.posts.comments.store');

Route::get('/pages', [PageController::class, 'index'])->name('api.v1.pages.index');
Route::get('/pages/{page}', [PageController::class, 'show'])->name('api.v1.pages.show');
