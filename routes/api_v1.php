<?php

use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\ChangePasswordController;
use App\Http\Controllers\Api\V1\CommentPostController;
use App\Http\Controllers\Api\V1\DeactivateAccountController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\FollowOrganizationController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\LikePostController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\LogoutController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OpportunityController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProgramController;
use App\Http\Controllers\Api\V1\RegisterIndividualController;
use App\Http\Controllers\Api\V1\RegisterOrganizationController;
use App\Http\Controllers\Api\V1\ResetPasswordController;
use App\Http\Controllers\Api\V1\SectorController;
use App\Http\Controllers\Api\V1\SkillController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\UserPreferencesController;
use App\Http\Middleware\ActiveUserMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register/individuals', [RegisterIndividualController::class, 'store'])->name('api.v1.auth.register.individuals');
Route::post('/auth/register/organizations', [RegisterOrganizationController::class, 'store'])->name('api.v1.auth.register.organizations');
Route::post('/auth/login', [LoginController::class, 'store'])->name('api.v1.auth.login');
Route::post('/auth/logout', [LogoutController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.auth.logout');
Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'store'])->name('api.v1.auth.forgot-password');
Route::post('/auth/reset-password', [ResetPasswordController::class, 'store'])->name('api.v1.auth.reset-password');
Route::post('/auth/change-password', [ChangePasswordController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.auth.change-password');
Route::post('/auth/deactivate', [DeactivateAccountController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.auth.deactivate')->withoutMiddleware(ActiveUserMiddleware::class);

Route::get('/me/preferences', [UserPreferencesController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.profile.preferences.index');
Route::patch('/me/preferences', [UserPreferencesController::class, 'update'])->middleware('auth:sanctum')->name('api.v1.profile.preferences.update');
Route::get('/me', [ProfileController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.profile.index');
Route::patch('/me', [ProfileController::class, 'update'])->middleware('auth:sanctum')->name('api.v1.profile.update');
// WARNING: Do NOT remove this POST route!
// PHP/Laravel cannot parse multipart/form-data for PATCH requests (see https://github.com/laravel/framework/issues/13457)
// This POST route is REQUIRED for mobile apps and file uploads using multipart/form-data.
// Always use PATCH for JSON, but use POST for file uploads or any multipart requests.
Route::post('/me', [ProfileController::class, 'update'])->middleware('auth:sanctum')->name('api.v1.profile.update.post');

Route::get('/feed/search', [FeedController::class, 'search'])->name('api.v1.feed.search');
Route::get('/feed/following', [FeedController::class, 'following'])->middleware('auth:sanctum')->name('api.v1.feed.following');
Route::get('/feed/recent', [FeedController::class, 'recent'])->name('api.v1.feed.recent');

Route::get('/organizations', [OrganizationController::class, 'index'])->name('api.v1.organizations.index');
// NOTE: /search needs to be above /{organization:slug} to avoid route conflict
Route::get('/organizations/search', [OrganizationController::class, 'search'])->name('api.v1.organizations.search');
Route::get('/organizations/{organization:slug}', [OrganizationController::class, 'show'])->name('api.v1.organizations.show');
Route::post('/organizations/{organization:slug}/follows', [FollowOrganizationController::class, 'store'])->name('api.v1.organizations.follows.store')->middleware('auth:sanctum');
Route::delete('/organizations/{organization:slug}/follows', [FollowOrganizationController::class, 'destroy'])->name('api.v1.organizations.follows.destroy')->middleware('auth:sanctum');

Route::get('/skills', [SkillController::class, 'index'])->name('api.v1.skills.index');
Route::get('/skills/{skill}', [SkillController::class, 'show'])->name('api.v1.skills.show');

Route::get('/sectors', [SectorController::class, 'index'])->name('api.v1.sectors.index');
Route::get('/sectors/{sector}', [SectorController::class, 'show'])->name('api.v1.sectors.show');

Route::get('/locations', [LocationController::class, 'index'])->name('api.v1.locations.index');
Route::get('/locations/{location}', [LocationController::class, 'show'])->name('api.v1.locations.show');

Route::get('/programs', [ProgramController::class, 'index'])->name('api.v1.programs.index');
Route::get('/programs/search', [ProgramController::class, 'search'])->name('api.v1.programs.search');
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('api.v1.programs.show');

Route::get('/opportunities', [OpportunityController::class, 'index'])->name('api.v1.opportunities.index');
Route::get('/opportunities/featured', [OpportunityController::class, 'featured'])->name('api.v1.opportunities.featured');
Route::get('/opportunities/stats', [OpportunityController::class, 'stats'])->name('api.v1.opportunities.stats');
Route::get('/opportunities/search', [OpportunityController::class, 'search'])->name('api.v1.opportunities.search');
Route::get('/opportunities/{id}', [OpportunityController::class, 'show'])->name('api.v1.opportunities.show');

Route::get('/applications', [ApplicationController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.applications.index');
Route::post('/applications', [ApplicationController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.applications.store');
Route::get('/applications/{id}', [ApplicationController::class, 'show'])->middleware('auth:sanctum')->name('api.v1.applications.show');
Route::patch('/applications/{id}', [ApplicationController::class, 'update'])->middleware('auth:sanctum')->name('api.v1.applications.update');
Route::delete('/applications/{id}', [ApplicationController::class, 'destroy'])->middleware('auth:sanctum')->name('api.v1.applications.destroy');

Route::get('/posts', [PostController::class, 'index'])->name('api.v1.posts.index');
Route::get('/posts/search', [PostController::class, 'search'])->name('api.v1.posts.search');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('api.v1.posts.show');

Route::post('/posts/{post:slug}/like', [LikePostController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.posts.likes.store');
Route::delete('/posts/{post:slug}/like', [LikePostController::class, 'destroy'])->middleware('auth:sanctum')->name('api.v1.posts.likes.destroy');

Route::get('/posts/{post:slug}/comments', [CommentPostController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.posts.comments.index');
Route::post('/posts/{post:slug}/comments', [CommentPostController::class, 'store'])->middleware('auth:sanctum')->name('api.v1.posts.comments.store');

Route::get('/pages', [PageController::class, 'index'])->name('api.v1.pages.index');
Route::get('/pages/{page}', [PageController::class, 'show'])->name('api.v1.pages.show');

Route::get('/support-tickets', [SupportTicketController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.support-tickets.index');
Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->middleware('auth:sanctum')->name('api.v1.support-tickets.show');
Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('api.v1.support-tickets.store');

Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.index');
Route::get('/notifications/{notification}', [NotificationController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.show');
Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.mark-as-read');
Route::patch('/notifications/read', [NotificationController::class, 'markAllAsRead'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.mark-all-as-read');
Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.destroy.single');
Route::delete('/notifications', [NotificationController::class, 'destroyAll'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.destroy.all');
