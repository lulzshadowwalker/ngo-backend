<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_organizations()
    {
        $organizations = Organization::factory()->count(3)->create();
        $resource = OrganizationResource::collection($organizations);

        $response = $this->getJson(route('api.organizations.index', ['language' => 'en']));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_organization()
    {
        $organization = Organization::factory()->create();
        $resource = OrganizationResource::make($organization);

        $response = $this->getJson(route('api.organizations.show', [
            'language' => 'en',
            'organization' => $organization->slug,
        ]));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }
}
