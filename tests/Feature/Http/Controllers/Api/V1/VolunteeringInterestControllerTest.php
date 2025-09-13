<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\VolunteeringInterestResource;
use App\Models\VolunteeringInterest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteeringInterestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_volunteering_interests()
    {
        $volunteeringInterests = VolunteeringInterest::factory()->count(3)->create();
        $resource = VolunteeringInterestResource::collection($volunteeringInterests);

        $response = $this->getJson(route('api.v1.volunteering-interests.index'));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_volunteering_interest()
    {
        $volunteeringInterest = VolunteeringInterest::factory()->create();
        $resource = VolunteeringInterestResource::make($volunteeringInterest);

        $response = $this->getJson(route('api.v1.volunteering-interests.show', [
            'volunteeringInterest' => $volunteeringInterest->id,
        ]));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }
}
