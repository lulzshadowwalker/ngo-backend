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
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Green Earth Foundation",
     *       "slug": "green-earth-foundation",
     *       "description": "Environmental conservation organization",
     *       "email": "contact@greenearth.org",
     *       "website": "https://greenearth.org",
     *       "phone": "+1234567890",
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
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": 1,
     *     "name": "Green Earth Foundation",
     *     "slug": "green-earth-foundation",
     *     "description": "Environmental conservation organization",
     *     "email": "contact@greenearth.org",
     *     "website": "https://greenearth.org",
     *     "phone": "+1234567890",
     *     "created_at": "2024-01-15T10:00:00.000000Z",
     *     "updated_at": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 404 scenario="Organization not found" {
     *   "message": "Organization not found"
     * }
     * 
     * @return OrganizationResource
     */
    public function show(Organization $organization)
    {
        return OrganizationResource::make($organization);
    }
}
