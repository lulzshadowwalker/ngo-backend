<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    /**
     * Change user password
     * 
     * Change the authenticated user's password by providing current password and new password.
     *
     * @group Authentication
     * @authenticated
     * 
     * @bodyParam data.attributes.current_password string required The user's current password. Example: currentPassword123
     * @bodyParam data.attributes.new_password string required The new password (minimum 8 characters). Example: newSecurePassword123
     * @bodyParam data.attributes.new_password_confirmation string required New password confirmation. Example: newSecurePassword123
     */
    public function store(Request $request)
    {
        $request->validate([
            'data.attributes.current_password' => 'required|string',
            'data.attributes.new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:data.attributes.current_password'
            ],
        ], [
            'data.attributes.new_password.confirmed' => 'The new password confirmation does not match.',
            'data.attributes.new_password.different' => 'The new password must be different from the current password.',
        ]);

        $user = $request->user();
        $currentPassword = $request->input('data.attributes.current_password');
        $newPassword = $request->input('data.attributes.new_password');

        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '422',
                        'title' => 'Validation Error',
                        'detail' => 'The current password is incorrect.',
                        'source' => [
                            'pointer' => '/data/attributes/current_password'
                        ]
                    ]
                ]
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Optionally, you might want to revoke all tokens to force re-login
        // $user->tokens()->delete();

        return response()->json([
            'data' => [
                'type' => 'password-change',
                'attributes' => [
                    'message' => 'Password has been changed successfully.',
                ]
            ]
        ], 200);
    }
}
