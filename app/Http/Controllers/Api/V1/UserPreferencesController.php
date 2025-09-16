<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateUserPreferencesRequest;
use App\Http\Resources\V1\UserPreferencesResource;
use Illuminate\Support\Facades\Auth;

class UserPreferencesController extends Controller
{
    /**
     * Get user preferences
     *
     * Retrieve the current user's preferences. If no preferences exist,
     * default preferences will be created and returned.
     *
     * @group User Management
     *
     * @authenticated
     */
    public function index()
    {
        $preferences = Auth::user()->preferences()->firstOrCreate([
            'user_id' => Auth::user()->id,
        ]);

        return UserPreferencesResource::make($preferences->fresh());
    }

    /**
     * Update user preferences
     *
     * Update the authenticated user's preferences. Only provided fields will be updated;
     * other preferences will remain unchanged.
     *
     * @group User Management
     *
     * @authenticated
     *
     * @bodyParam language string optional The preferred language (en, es, fr, etc.). Example: en
     * @bodyParam email_notifications boolean optional Enable/disable email notifications. Example: true
     * @bodyParam push_notifications boolean optional Enable/disable push notifications. Example: false
     * @bodyParam profile_visibility string optional Profile visibility (public, private, friends). Example: public
     */
    public function update(UpdateUserPreferencesRequest $request)
    {
        $preferences = Auth::user()->preferences()->updateOrCreate(
            ['user_id' => Auth::user()->id],
            $request->mappedAttributes()->toArray(),
        );

        if (Auth::user()->isIndividual && $request->profileVisibility()) {
            Auth::user()->individual->preferences()->updateOrCreate(
                ['individual_id' => Auth::user()->individual->id],
                ['profile_visibility' => $request->profileVisibility()->value],
            );
        }

        return UserPreferencesResource::make($preferences);
    }
}
