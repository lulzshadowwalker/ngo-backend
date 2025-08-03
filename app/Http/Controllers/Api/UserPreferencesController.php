<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPreferencesRequest;
use App\Http\Resources\UserPreferencesResource;
use Illuminate\Support\Facades\Auth;

class UserPreferencesController extends Controller
{
    public function index()
    {
        $preferences = Auth::user()->preferences()->firstOrCreate([
            'user_id' => Auth::user()->id,
        ]);

        return UserPreferencesResource::make($preferences->fresh());
    }

    public function update(UpdateUserPreferencesRequest $request)
    {
        $preferences = Auth::user()->preferences()->updateOrCreate(
            ['user_id' => Auth::user()->id],
            $request->mappedAttributes()->toArray(),
        );

        return UserPreferencesResource::make($preferences);
    }
}
