<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationController extends Controller
{
    /**
     * List all organizations
     * 
     * Retrieve a list of all registered organizations in the system.
     * This endpoint provides information about NGOs including their basic details,
     * contact information, and operational status.
     *
     * @group Organizations
     * @unauthenticated
     * 
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return OrganizationResource::collection(Organization::all());
    }

    /**
     * Get organization details
     * 
     * Retrieve detailed information about a specific organization using its slug.
     * This includes comprehensive information about the organization's mission,
     * contact details, and operational information.
     *
     * @group Organizations
     * @unauthenticated
     * 
     * @urlParam organization string required The slug of the organization. Example: green-earth-foundation
     * 
     * @return OrganizationResource
     */
    public function show(Organization $organization)
    {
        return OrganizationResource::make($organization);
    }
}
