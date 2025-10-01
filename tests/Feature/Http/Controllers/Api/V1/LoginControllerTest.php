<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_in_user_with_valid_credentials()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('securePassword123'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'email' => $user->email,
                    'password' => 'securePassword123',
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
    }

    public function test_it_does_not_log_in_user_with_invalid_credentials()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('securePassword123'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'email' => $user->email,
                    'password' => 'wrongPassword',
                ],
            ],
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_user_can_send_device_token_with_login_request()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('securePassword123'),
        ]);

        $deviceToken = 'sample_device_token_123';

        $response = $this->postJson(route('api.v1.auth.login'), [
            'data' => [
                'attributes' => [
                    'email' => $user->email,
                    'password' => 'securePassword123',
                ],
                'relationships' => [
                    'deviceTokens' => [
                        'data' => [
                            'attributes' => [
                                'token' => $deviceToken,
                            ],
                        ],
                    ],
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

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => $deviceToken,
        ]);
    }
}
