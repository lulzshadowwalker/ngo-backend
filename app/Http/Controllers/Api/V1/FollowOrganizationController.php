<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class FollowOrganizationController extends Controller
{
    /**
     * Follow an organization
     * 
     * Follow an organization to receive updates about their activities and posts.
     * If already following, this endpoint will have no effect.
     *
     * @group Organizations
     * @authenticated
     * 
     * @urlParam organization string required The slug of the organization to follow. Example: save-the-whales
     */
    public function store(Organization $organization)
    {
        Follow::create([
            'followable_id' => $organization->id,
            'followable_type' => Organization::class,
            'user_id' => Auth::id(),
        ]);

        return response()->noContent(204);
    }

    /**
     * Unfollow an organization
     * 
     * Stop following an organization. The user will no longer receive updates
     * about the organization's activities and posts.
     *
     * @group Organizations
     * @authenticated
     * 
     * @urlParam organization string required The slug of the organization to unfollow. Example: save-the-whales
     */
    public function destroy(Organization $organization)
    {
        Follow::where([
            'followable_id' => $organization->id,
            'followable_type' => Organization::class,
            'user_id' => Auth::id(),
        ])->delete();

        return response()->noContent(204);
    }
}
