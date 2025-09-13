<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomResetPasswordNotification;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_password_reset_link_to_valid_email()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => [
                'attributes' => [
                    'email' => 'test@example.com',
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

        Notification::assertSentTo($user, CustomResetPasswordNotification::class);
    }

    public function test_it_returns_success_for_nonexistent_email_for_security()
    {
        Notification::fake();

        $response = $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => [
                'attributes' => [
                    'email' => 'nonexistent@example.com',
                ],
            ],
        ]);

        // Should return success to prevent email enumeration attacks
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'password-reset-request',
                    'attributes' => [
                        'message' => 'Password reset link sent to your email address.',
                    ],
                ],
            ]);

        Notification::assertNothingSent();
    }

    public function test_it_validates_required_email_field()
    {
        $response = $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => [
                'attributes' => [],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_it_validates_email_format()
    {
        $response = $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => [
                'attributes' => [
                    'email' => 'invalid-email-format',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_it_handles_throttling_appropriately()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // First request should succeed
        $response = $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => [
                'attributes' => [
                    'email' => 'test@example.com',
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Second immediate request should be throttled
        $response = $this->postJson(route('api.v1.auth.forgot-password'), [
            'data' => [
                'attributes' => [
                    'email' => 'test@example.com',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }
}
