<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return LocationResource::collection(Location::all());
    }

    /**
     * @return LocationResource
     */
    public function show(string $language, Location $location)
    {
        return LocationResource::make($location);
    }
}
