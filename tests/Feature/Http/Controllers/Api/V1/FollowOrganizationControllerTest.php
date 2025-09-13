<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Individual;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowOrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_an_organization()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('api.v1.organizations.follows.store', $organization));

        $response->assertNoContent(204);

        $this->assertDatabaseHas('follows', [
            'followable_id' => $organization->id,
            'followable_type' => Organization::class,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($user->follows()->where('followable_id', $organization->id)->exists());
    }

    public function test_user_can_unfollow_an_organization()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('api.v1.organizations.follows.store', $organization));

        $response = $this->delete(route('api.v1.organizations.follows.destroy', $organization));

        $response->assertNoContent(204);

        $this->assertDatabaseMissing('follows', [
            'followable_id' => $organization->id,
            'followable_type' => Organization::class,
            'user_id' => $user->id,
        ]);

        $this->assertFalse($user->follows()->where('followable_id', $organization->id)->exists());
    }
}
