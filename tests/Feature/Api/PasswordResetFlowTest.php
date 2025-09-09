<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_password_reset_flow()
    {
        Notification::fake();
        
        // Create a user
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('oldPassword123'),
        ]);

        // Step 1: Request password reset
        $response = $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => [
                'attributes' => [
                    'email' => 'user@example.com',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'password-reset-request',
                    'attributes' => [
                        'message' => 'Password reset link sent to your email address.',
                    ],
                ],
            ]);

        // Verify notification was sent
        Notification::assertSentTo($user, ResetPassword::class);

        // Step 2: Generate a token (simulating what would be in the email)
        $token = Password::createToken($user);

        // Step 3: Reset the password using the token
        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $token,
                    'email' => 'user@example.com',
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'password-reset',
                    'attributes' => [
                        'message' => 'Password has been reset successfully.',
                    ],
                ],
            ]);

        // Step 4: Verify the password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newPassword123', $user->password));
        $this->assertFalse(Hash::check('oldPassword123', $user->password));

        // Step 5: Verify the user can login with the new password
        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'email' => 'user@example.com',
                    'password' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'attributes' => [
                        'token',
                        'role',
                    ],
                ],
            ]);

        // Step 6: Verify the user cannot login with the old password
        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'email' => 'user@example.com',
                    'password' => 'oldPassword123',
                ],
            ],
        ]);

        $response->assertStatus(401);

        // Step 7: Verify the token was consumed (cannot be used again)
        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $token,
                    'email' => 'user@example.com',
                    'password' => 'anotherPassword123',
                    'password_confirmation' => 'anotherPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_password_reset_tokens_are_user_specific()
    {
        Notification::fake();
        
        $userA = User::factory()->create(['email' => 'usera@example.com']);
        $userB = User::factory()->create(['email' => 'userb@example.com']);

        // Request reset for User A
        $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => ['attributes' => ['email' => 'usera@example.com']],
        ]);

        // Generate token for User A
        $tokenA = Password::createToken($userA);

        // Try to use User A's token for User B - should fail
        $response = $this->postJson(route('api.v1.auth.reset-password'), [
            'data' => [
                'attributes' => [
                    'token' => $tokenA,
                    'email' => 'userb@example.com', // Wrong email
                    'password' => 'newPassword123',
                    'password_confirmation' => 'newPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }
}
