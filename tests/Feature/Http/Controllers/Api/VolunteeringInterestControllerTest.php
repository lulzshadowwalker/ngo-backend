<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\VolunteeringInterestResource;
use App\Models\VolunteeringInterest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteeringInterestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_volunteering_interests()
    {
        $volunteeringInterests = VolunteeringInterest::factory()
            ->count(3)
            ->create();
        $resource = VolunteeringInterestResource::collection(
            $volunteeringInterests
        );

        $response = $this->getJson(
            route("api.volunteering-interests.index", ["language" => "en"])
        );

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_volunteering_interest()
    {
        $volunteeringInterest = VolunteeringInterest::factory()->create();
        $resource = VolunteeringInterestResource::make($volunteeringInterest);

        $response = $this->getJson(
            route("api.volunteering-interests.show", [
                "language" => "en",
                "volunteeringInterest" => $volunteeringInterest->id,
            ])
        );

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }
}
