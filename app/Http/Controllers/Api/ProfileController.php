<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndividualResource;
use Exception;
// use App\Http\Resources\ClientResource;

class ProfileController extends Controller
{
    /**
     * Get current user profile
     * 
     * Retrieve the authenticated user's profile information. Returns different
     * data structures based on the user type (individual, organization, etc.).
     *
     * @group User Management
     * @authenticated
     */
    public function index()
    {
        $user = auth()->user();

        switch (true) {
            case $user->isIndividual:
                return IndividualResource::make($user->individual);
            case $user->isOrganizer:
                throw new Exception('User type not recognized');
            default:
                throw new Exception('User type not recognized');
        }
    }
}
