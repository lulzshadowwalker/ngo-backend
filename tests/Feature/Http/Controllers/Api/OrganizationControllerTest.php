<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_organizations()
    {
        $organizations = Organization::factory()->count(3)->create();
        $resource = OrganizationResource::collection($organizations);

        $response = $this->getJson(route('api.v1.organizations.index'));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_organization()
    {
        $organization = Organization::factory()->create();
        $resource = OrganizationResource::make($organization);

        $response = $this->getJson(route('api.v1.organizations.show', [
            'organization' => $organization->slug,
        ]));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_returns_correct_follow_status_when_the_does_not_follow_an_organization()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $organization = Organization::factory()->create();

        $response = $this->getJson(route('api.v1.organizations.show', [
            'organization' => $organization->slug,
        ]));

        $response->assertOk();
        $response->assertJsonPath('data.attributes.following', false);
    }

    public function test_it_returns_correct_follow_status_when_the_user_follows_an_organization()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $organization = Organization::factory()->create();
        
        // Create a follow relationship using the Follow model
        $organization->follows()->create(['user_id' => $user->id]);

        $response = $this->getJson(route('api.v1.organizations.show', [
            'organization' => $organization->slug,
        ]));

        $response->assertOk();
        $response->assertJsonPath('data.attributes.following', true);
    }
}
