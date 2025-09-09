<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(route('filament.admin.pages.dashboard')));

// Password reset routes for email links
Route::get('/password/reset/{token}', [\App\Http\Controllers\Web\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Web\PasswordResetController::class, 'reset'])->name('password.update');
