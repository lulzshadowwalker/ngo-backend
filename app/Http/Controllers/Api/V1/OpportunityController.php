<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OpportunityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchOpportunityRequest;
use App\Http\Resources\V1\OpportunityResource;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    /**
     * List Opportunities
     *
     * Display a paginated list of active opportunities with filtering.
     *
     * @group Opportunities
     * @unauthenticated
     *
     * @queryParam location string Filter by location description. Example: "New York"
     * @queryParam latitude float Latitude for radius search. Example: 40.7128
     * @queryParam longitude float Longitude for radius search. Example: -74.0060
     * @queryParam radius integer Radius in kilometers for location search. Example: 10
     * @queryParam tags string Comma-separated list of tags to filter by. Example: "education,health"
     * @queryParam skills string Comma-separated list of skills to filter by. Example: "php,project management"
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     * @queryParam sector_id integer Filter by sector ID. Example: 2
     * @queryParam max_duration integer Filter by maximum duration. Example: 30
     * @queryParam expiry_date_from date Filter by expiry date from. Example: "2025-12-01"
     * @queryParam expiry_date_to date Filter by expiry date to. Example: "2025-12-31"
     * @queryParam search string Search in title and description. Example: "volunteer"
     * @queryParam sort_by string Sort field. Allowed: 'created_at', 'expiry_date', 'duration'. Default: 'created_at'. Example: "expiry_date"
     * @queryParam sort_direction string Sort direction. Allowed: 'asc', 'desc'. Default: 'desc'. Example: "asc"
     * @queryParam per_page integer Number of items per page. Default: 20. Max: 100. Example: 50
     */
    public function index(Request $request): JsonResponse
    {
        $query = Opportunity::query()
            ->with(['organization:id,name', 'program:id,title', 'sector:id,name'])
            ->where('status', OpportunityStatus::Active)
            ->where('expiry_date', '>', now());

        // Location-based filtering
        if ($request->filled('location')) {
            $query->where(function ($q) use ($request) {
                $location = $request->location;
                $q->whereJsonContains('location_description', $location);
            });
        }

        // Coordinate-based filtering (within radius)
        if ($request->filled(['latitude', 'longitude', 'radius'])) {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $radius = $request->radius; // in kilometers

            $query->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw(
                    "ST_Distance_Sphere(
                        POINT(longitude, latitude),
                        POINT(?, ?)
                    ) <= ?",
                    [$lng, $lat, $radius * 1000] // Convert km to meters
                );
        }

        // Tags filtering
        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            foreach ($tags as $tag) {
                $query->where(function ($q) use ($tag) {
                    $q->whereJsonContains('tags->en', $tag)
                        ->orWhereJsonContains('tags->ar', $tag);
                });
            }
        }

        // Skills filtering
        if ($request->filled('skills')) {
            $skills = is_array($request->skills) ? $request->skills : explode(',', $request->skills);
            foreach ($skills as $skill) {
                $query->whereJsonContains('required_skills', $skill);
            }
        }

        // Organization filtering
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Sector filtering
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        // Duration filtering
        if ($request->filled('max_duration')) {
            $query->where('duration', '<=', $request->max_duration);
        }

        // Date range filtering
        if ($request->filled('expiry_date_from')) {
            $query->where('expiry_date', '>=', $request->expiry_date_from);
        }

        if ($request->filled('expiry_date_to')) {
            $query->where('expiry_date', '<=', $request->expiry_date_to);
        }

        // Search in title and description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereJsonContains('title->en', $search)
                    ->orWhereJsonContains('title->ar', $search)
                    ->orWhereJsonContains('description->en', $search)
                    ->orWhereJsonContains('description->ar', $search);
            });
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortFields = ['created_at', 'expiry_date', 'duration'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100); // Max 100 per page
        $opportunities = $query->paginate($perPage);

        return response()->json([
            'data' => OpportunityResource::collection($opportunities->items()),
            'meta' => [
                'total' => $opportunities->total(),
                'perPage' => $opportunities->perPage(),
                'currentPage' => $opportunities->currentPage(),
                'lastPage' => $opportunities->lastPage(),
                'from' => $opportunities->firstItem(),
                'to' => $opportunities->lastItem(),
            ],
            'links' => [
                'first' => $opportunities->url(1),
                'last' => $opportunities->url($opportunities->lastPage()),
                'prev' => $opportunities->previousPageUrl(),
                'next' => $opportunities->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Get Opportunity
     *
     * Display the specified opportunity with full details.
     *
     * @group Opportunities
     * @unauthenticated
     *
     * @urlParam id string required The ID of the opportunity. Example: 1
     */
    public function show(string $id): JsonResponse
    {
        $opportunity = Opportunity::with([
            'organization:id,name,bio,website,location_id',
            'program:id,title,description',
            'sector:id,name',
            'applicationForm.formFields' => function ($query) {
                $query->orderBy('sort_order');
            }
        ])
            ->where('status', OpportunityStatus::Active)
            ->findOrFail($id);

        views($opportunity)->record();

        return response()->json([
            'data' => new OpportunityResource($opportunity),
        ]);
    }

    /**
     * Search Opportunities
     *
     * @group Opportunities
     * @unauthenticated
     *
     * @queryParam query string The search term. Example: "developer"
     * @queryParam sector integer Filter by sector ID. Example: 1
     */
    public function search(SearchOpportunityRequest $request)
    {
        $query = $request->input('query', '') ?? '';

        $query = Opportunity::search($query);

        $query->when($request->has('sector'), function ($q) use ($request) {
            $q->where('sector_id', (int) $request->input('sector'));
        });

        $opportunities = $query->get();

        $opportunities->load(['organization', 'sector', 'program', 'applicationForm']);

        return OpportunityResource::collection($opportunities);
    }

    /**
     * Get Featured Opportunities
     *
     * Get a list of featured or recommended opportunities.
     *
     * @group Opportunities
     * @unauthenticated
     *
     * @queryParam limit integer The number of featured opportunities to return. Default: 6. Example: 4
     */
    public function featured(Request $request): JsonResponse
    {
        $opportunities = Opportunity::query()
            ->with(['organization:id,name', 'program:id,title'])
            ->where('status', OpportunityStatus::Active)
            ->where('expiry_date', '>', now())
            ->where('is_featured', true) // Assuming we add this column
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 6))
            ->get();

        return response()->json([
            'data' => OpportunityResource::collection($opportunities),
        ]);
    }

    /**
     * Get Opportunity Stats
     *
     * Get statistics for opportunities for the homepage.
     *
     * @group Opportunities
     * @unauthenticated
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_opportunities' => Opportunity::where('status', OpportunityStatus::Active)->count(),
            'total_organizations' => Opportunity::distinct('organization_id')->count(),
            'opportunities_this_month' => Opportunity::where('status', OpportunityStatus::Active)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }
}
