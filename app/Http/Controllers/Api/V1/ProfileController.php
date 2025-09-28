<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateIndividualProfileRequest;
use App\Http\Requests\V1\UpdateOrganizationProfileRequest;
use App\Http\Resources\V1\IndividualResource;
use App\Http\Resources\V1\OrganizationProfileResource;
use App\Models\Organization;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// use App\Http\Resources\V1\ClientResource;

class ProfileController extends Controller
{
    /**
     * Get current user profile
     *
     * Retrieve the authenticated user's profile information. Returns different
     * data structures based on the user type (individual, organization, etc.).
     *
     * @group User Management
     *
     * @authenticated
     */
    public function index()
    {
        $user = Auth::user();

        switch (true) {
            case $user->isIndividual:
                return IndividualResource::make($user->individual->load(['location', 'skills']));
            case $user->isOrganizer:
                return OrganizationProfileResource::make($user->organization->load(['location', 'sector']));
            default:
                throw new Exception('User type not recognized');
        }
    }

    /**
     * Update the profile.
     *
     * @group User Management
     *
     * @authenticated
     */
    public function update()
    {
        $user = Auth::user();

        // Handle file uploads first (outside transaction)
        $avatarFile = null;
        $logoFile = null;

        if ($user->isIndividual) {
            $request = app(UpdateIndividualProfileRequest::class);
            if ($request->avatar()) {
                $avatarFile = $request->avatar();
            }
        } elseif ($user->isOrganizer) {
            $request = app(UpdateOrganizationProfileRequest::class);
            if ($request->logo()) {
                $logoFile = $request->logo();
            }
        }

        return DB::transaction(function () use ($user, $avatarFile, $logoFile) {
            switch (true) {
                case $user->isIndividual:
                    return $this->updateIndividual($user, $avatarFile);
                case $user->isOrganizer:
                    return $this->updateOrganization($user, $logoFile);
                default:
                    throw new Exception('User type not recognized');
            }
        });
    }

    private function updateIndividual(User $user, $avatarFile = null)
    {
        $request = UpdateIndividualProfileRequest::createFrom(request());
        $request->setContainer(app())->validateResolved();

        $individual = $user->individual;

        // Update User fields
        if ($request->name()) {
            $user->name = $request->name();
        }
        if ($request->email()) {
            $user->email = $request->email();
        }
        $user->save();

        // Update Individual fields
        if ($request->bio()) {
            $individual->bio = $request->bio();
        }
        if ($request->birthdate()) {
            $individual->birthdate = $request->birthdate();
        }
        if ($request->has('data.relationships.location.data.id')) {
            $individual->location_id = $request->location();
        }
        $individual->save();

        // Handle avatar upload
        if ($avatarFile && file_exists($avatarFile->getRealPath())) {
            $user->clearMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            $user->addMedia($avatarFile)
                ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
        }

        // Handle skills
        if ($request->has('data.relationships.skills.data')) {
            $individual->skills()->sync($request->skills());
        }

        // Handle sectors
        if ($request->has('data.relationships.sectors.data')) {
            $individual->sectors()->sync($request->sectors());
        }

        return IndividualResource::make($individual->load(['location', 'skills']));
    }

    private function updateOrganization(User $user, $logoFile = null)
    {
        $request = UpdateOrganizationProfileRequest::createFrom(request());
        $request->setContainer(app())->validateResolved();

        $organization = $user->organization;

        // Update User fields
        if ($request->name()) {
            $user->name = $request->name();
            $organization->name = $request->name();
        }
        if ($request->email()) {
            $user->email = $request->email();
        }
        $user->save();

        // Update Organization fields
        if ($request->bio()) {
            $organization->bio = $request->bio();
        }
        if ($request->website()) {
            $organization->website = $request->website();
        }
        if ($request->contactEmail()) {
            $organization->contact_email = $request->contactEmail();
        }
        if ($request->has('data.relationships.location.data.id')) {
            $organization->location_id = $request->location();
        }
        if ($request->has('data.relationships.sector.data.id')) {
            $organization->sector_id = $request->sector();
        }
        $organization->save();

        // Handle logo upload
        if ($logoFile && file_exists($logoFile->getRealPath())) {
            $user->clearMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            $organization->clearMediaCollection(Organization::MEDIA_COLLECTION_LOGO);

            $user->addMedia($logoFile)
                ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            $organization->addMedia($logoFile)
                ->toMediaCollection(Organization::MEDIA_COLLECTION_LOGO);
        }

        return OrganizationProfileResource::make($organization->load(['location', 'sector']));
    }
}
