<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class DeactivateAccountControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_deactivates_an_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson(route('api.v1.auth.deactivate'));

        $response->assertNoContent();

        $user->refresh();

        $this->assertEquals(UserStatus::inactive, $user->status);
        $this->assertNotNull($user->deactivated_at);
    }
}
