<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPreferencesRequest;
use App\Http\Resources\UserPreferencesResource;
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
     * @authenticated
     * 
     * @bodyParam language string optional The preferred language (en, es, fr, etc.). Example: en
     * @bodyParam appearance string optional The appearance theme (light, dark, auto). Example: dark
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

        return UserPreferencesResource::make($preferences);
    }
}
