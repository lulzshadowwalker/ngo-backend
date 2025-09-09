<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Send password reset link
     * 
     * Send a password reset link to the user's email address.
     *
     * @group Authentication
     * @unauthenticated
     * 
     * @bodyParam data.attributes.email string required The user's email address. Example: john.doe@example.com
     */
    public function store(Request $request)
    {
        $request->validate([
            'data.attributes.email' => 'required|email',
        ]);

        $email = $request->input('data.attributes.email');

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'data.attributes.email' => [__($status)],
            ]);
        }

        return response()->json([
            'data' => [
                'type' => 'password-reset-request',
                'attributes' => [
                    'message' => 'Password reset link sent to your email address.',
                ]
            ]
        ], 200);
    }
}
