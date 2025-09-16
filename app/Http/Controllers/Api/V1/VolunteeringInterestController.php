<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\VolunteeringInterestResource;
use App\Models\VolunteeringInterest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VolunteeringInterestController extends Controller
{
    /**
     * List all volunteering interests
     *
     * Retrieve a list of all available volunteering interests in the system.
     * Volunteering interests are used to categorize user expertise and organization needs.
     *
     * @group Skills & Locations
     *
     * @unauthenticated
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return VolunteeringInterestResource::collection(VolunteeringInterest::all());
    }

    /**
     * Get volunteering interest details
     *
     * Retrieve detailed information about a specific volunteering interest,
     * including its description and related information.
     *
     * @group Skills & Locations
     *
     * @unauthenticated
     *
     * @urlParam volunteeringInterest integer required The ID of the volunteering interest. Example: 1
     *
     * @return VolunteeringInterestResource
     */
    public function show(VolunteeringInterest $volunteeringInterest)
    {
        return VolunteeringInterestResource::make($volunteeringInterest);
    }
}
