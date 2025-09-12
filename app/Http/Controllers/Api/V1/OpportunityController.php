<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OpportunityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchOpportunityRequest;
use App\Http\Requests\SearchPostRequest;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    /**
     * Display a paginated list of active opportunities with filtering
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
     * Display the specified opportunity with full details
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

        return response()->json([
            'data' => new OpportunityResource($opportunity),
        ]);
    }

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
     * Get featured/recommended opportunities
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
     * Get opportunities statistics for homepage
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
