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
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "New York City",
     *       "slug": "new-york-city",
     *       "country": "United States",
     *       "state": "New York",
     *       "city": "New York City",
     *       "latitude": 40.7128,
     *       "longitude": -74.0060,
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:00:00.000000Z"
     *     },
     *     {
     *       "id": 2,
     *       "name": "London",
     *       "slug": "london",
     *       "country": "United Kingdom",
     *       "state": "England",
     *       "city": "London",
     *       "latitude": 51.5074,
     *       "longitude": -0.1278,
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
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
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": 1,
     *     "name": "New York City",
     *     "slug": "new-york-city",
     *     "country": "United States",
     *     "state": "New York",
     *     "city": "New York City",
     *     "latitude": 40.7128,
     *     "longitude": -74.0060,
     *     "timezone": "America/New_York",
     *     "created_at": "2024-01-15T10:00:00.000000Z",
     *     "updated_at": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 404 scenario="Location not found" {
     *   "message": "Location not found"
     * }
     * 
     * @return LocationResource
     */
    public function show(Location $location)
    {
        return LocationResource::make($location);
    }
}
