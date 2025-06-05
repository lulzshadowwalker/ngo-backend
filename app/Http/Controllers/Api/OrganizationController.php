<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return OrganizationResource::collection(Organization::all());
    }

    /**
     * @return OrganizationResource
     */
    public function show(string $language, Organization $organization)
    {
        return OrganizationResource::make($organization);
    }
}
