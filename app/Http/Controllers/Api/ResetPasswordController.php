<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Reset password
     * 
     * Reset user password using the token sent via email.
     *
     * @group Authentication
     * @unauthenticated
     * 
     * @bodyParam data.attributes.token string required The password reset token. Example: abc123def456
     * @bodyParam data.attributes.email string required The user's email address. Example: john.doe@example.com
     * @bodyParam data.attributes.password string required The new password (minimum 8 characters). Example: newSecurePassword123
     * @bodyParam data.attributes.password_confirmation string required Password confirmation. Example: newSecurePassword123
     */
    public function store(Request $request)
    {
        $request->validate([
            'data.attributes.token' => 'required|string',
            'data.attributes.email' => 'required|email',
            'data.attributes.password' => 'required|string|min:8|confirmed',
        ], [
            'data.attributes.password.confirmed' => 'The password confirmation does not match.',
        ]);

        $credentials = [
            'token' => $request->input('data.attributes.token'),
            'email' => $request->input('data.attributes.email'),
            'password' => $request->input('data.attributes.password'),
            'password_confirmation' => $request->input('data.attributes.password_confirmation'),
        ];

        $status = Password::reset($credentials, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'data.attributes.email' => [__($status)],
            ]);
        }

        return response()->json([
            'data' => [
                'type' => 'password-reset',
                'attributes' => [
                    'message' => 'Password has been reset successfully.',
                ]
            ]
        ], 200);
    }
}
