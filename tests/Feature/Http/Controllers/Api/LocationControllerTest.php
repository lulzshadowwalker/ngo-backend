<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_locations()
    {
        $locations = Location::factory()->count(3)->create();
        $resource = LocationResource::collection($locations);

        $response = $this->getJson(route('api.locations.index'));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_location()
    {
        $location = Location::factory()->create();
        $resource = LocationResource::make($location);

        $response = $this->getJson(route('api.locations.show', [
            'location' => $location->id,
        ]));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }
}
