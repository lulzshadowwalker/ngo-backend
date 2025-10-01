<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_out()
    {
        $user = User::factory()->has(DeviceToken::factory())->create();
        $this->actingAs($user);

        $this->assertNotEmpty($user->deviceTokens);

        $this->post(route('api.v1.auth.logout'), [
            'data' => [
                'relationships' => [
                    'deviceTokens' => [
                        'data' => [
                            'attributes' => [
                                'token' => $user->deviceTokens->first()->token,
                            ],
                        ],
                    ],
                ],
            ],
            // 'deviceToken' => $user->deviceTokens->first()->token,
        ])->assertOk();

        $user->refresh();
        $this->assertEmpty($user->deviceTokens);
    }
}
