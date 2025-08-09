<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Organization;

class FollowOrganizationController extends Controller
{
    public function store(Organization $organization)
    {
        Follow::create([
            'followable_id' => $organization->id,
            'followable_type' => Organization::class,
            'user_id' => auth()->id(),
        ]);

        return response()->noContent(204);
    }

    public function destroy(Organization $organization)
    {
        Follow::where([
            'followable_id' => $organization->id,
            'followable_type' => Organization::class,
            'user_id' => auth()->id(),
        ])->delete();

        return response()->noContent(204);
    }
}
