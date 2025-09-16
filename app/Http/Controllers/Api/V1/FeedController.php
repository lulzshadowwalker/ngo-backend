<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchFeedRequest;
use App\Http\Resources\V1\OpportunityResource;
use App\Http\Resources\V1\OrganizationResource;
use App\Http\Resources\V1\PostResource;
use App\Http\Resources\V1\ProgramResource;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    /**
     * Get Following Feed
     *
     * Get the feed for organizations the user follows.
     *
     * @group Feed
     *
     * @authenticated
     */
    public function following()
    {
        $user = Auth::user();

        // Get followed organization IDs
        $followedOrgIds = $user->follows()
            ->where('followable_type', Organization::class)
            ->pluck('followable_id');

        $posts = Post::whereIn('organization_id', $followedOrgIds)
            ->with(['organization', 'likes', 'comments'])
            ->latest()
            ->get();

        $opportunities = Opportunity::whereIn('organization_id', $followedOrgIds)
            ->with(['organization', 'program', 'location', 'sector'])
            ->latest()
            ->limit(3)
            ->get();

        $profileCompletion = $user->individual?->profileCompletion;

        return response()->json([
            'posts' => PostResource::collection($posts),
            'opportunities' => OpportunityResource::collection($opportunities),
            'profileCompletion' => $profileCompletion,
        ]);
    }

    /**
     * Get Recent Feed
     *
     * Get the most recent posts and opportunities.
     *
     * @group Feed
     *
     * @unauthenticated
     */
    public function recent()
    {
        $posts = Post::with(['organization', 'likes', 'comments'])
            ->latest()
            ->get();

        $opportunities = Opportunity::with(['organization', 'program', 'location', 'sector'])
            ->latest()
            ->limit(3)
            ->get();

        return response()->json([
            'posts' => PostResource::collection($posts),
            'opportunities' => OpportunityResource::collection($opportunities),
        ]);
    }

    /**
     * Search Feed
     *
     * Search for organizations, programs, or opportunities.
     *
     * @group Feed
     *
     * @unauthenticated
     *
     * @queryParam query string required The search term. Example: "tech"
     * @queryParam type string Filter by type. Can be one of `organization`, `program`, `opportunity`. Example: "organization"
     */
    public function search(SearchFeedRequest $request)
    {
        $query = $request->input('query', '');
        $type = $request->input('type');

        $searchTypes = $type ? [$type] : ['organization', 'program', 'opportunity'];

        $results = [
            'organizations' => [],
            'programs' => [],
            'opportunities' => [],
        ];

        foreach ($searchTypes as $searchType) {
            switch ($searchType) {
                case 'organization':
                    $results['organizations'] = OrganizationResource::collection(Organization::search($query)->get());
                    break;
                case 'program':
                    $results['programs'] = ProgramResource::collection(Program::search($query)->get());
                    break;
                case 'opportunity':
                    $results['opportunities'] = OpportunityResource::collection(Opportunity::search($query)->get());
                    break;
            }
        }

        return response()->json($results);
    }
}
