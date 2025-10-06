<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(route('filament.admin.pages.dashboard')));

// Password reset routes for email links
Route::get('/password/reset/{token}', [\App\Http\Controllers\Web\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Web\PasswordResetController::class, 'reset'])->name('password.update');

// Application file downloads (admin only)
Route::get('/cms/applications/{application}/download/{response}', [\App\Http\Controllers\Web\ApplicationFileController::class, 'download'])
    ->name('application.download-file')
    ->middleware(['auth', 'role:organization']);
