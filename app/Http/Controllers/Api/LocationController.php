<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    /**
     * List all locations
     * 
     * Retrieve a list of all available locations in the system.
     * Locations are used for user profiles and organization addresses.
     *
     * @group Skills & Locations
     * @unauthenticated
     * 
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return LocationResource::collection(Location::all());
    }

    /**
     * Get location details
     * 
     * Retrieve detailed information about a specific location,
     * including geographic coordinates and administrative details.
     *
     * @group Skills & Locations
     * @unauthenticated
     * 
     * @urlParam location integer required The ID of the location. Example: 1
     * 
     * @return LocationResource
     */
    public function show(Location $location)
    {
        return LocationResource::make($location);
    }
}
