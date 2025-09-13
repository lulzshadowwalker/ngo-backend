<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SectorResource;
use App\Models\Sector;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SectorController extends Controller
{
    /**
     * List all sectors
     *
     * Retrieve a list of all available sectors in the system.
     *
     * @group Sectors
     * @unauthenticated
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return SectorResource::collection(Sector::all());
    }

    /**
     * Get sector details
     *
     * Retrieve detailed information about a specific sector.
     *
     * @group Sectors
     * @unauthenticated
     *
     * @urlParam sector integer required The ID of the sector. Example: 1
     *
     * @return SectorResource
     */
    public function show(Sector $sector)
    {
        return SectorResource::make($sector);
    }
}
