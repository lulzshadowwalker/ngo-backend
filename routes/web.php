<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(route('filament.admin.pages.dashboard')));

// Password reset route for email links
Route::get('/password/reset/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
})->name('password.reset');
