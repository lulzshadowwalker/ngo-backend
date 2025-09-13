<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchOrganizationRequest;
use App\Http\Resources\V1\OrganizationResource;
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
        return OrganizationResource::collection(Organization::with('location', 'sector')->get());
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
        $organization->load('location', 'sector');
        return OrganizationResource::make($organization);
    }

    /**
     * Search organizations
     *
     * Search for organizations based on a query string. This endpoint allows users
     * to find organizations by name or description, facilitating easier discovery of NGOs.
     *
     * @group Organizations
     * @unauthenticated
     *
     * @queryParam query string required The search query string. Example: health
     *
     * @return AnonymousResourceCollection
     */
    public function search(SearchOrganizationRequest $request)
    {
        $query = $request->input('query', '');

        $query = Organization::search($query ?? '');

        $query->when($request->has('sector'), function ($q) use ($request) {
            $q->where('sector_id', (int) $request->input('sector'));
        });
        $query->when($request->has('location'), function ($q) use ($request) {
            $q->where('location_id', (int) $request->input('location'));
        });

        $organizations = $query->get();

        return OrganizationResource::collection($organizations);
    }
}
