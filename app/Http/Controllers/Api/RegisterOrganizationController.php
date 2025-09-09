<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterOrganizationRequest;
use App\Http\Resources\AuthTokenResource;
use App\Models\User;
use App\Support\AccessToken;
use Illuminate\Support\Facades\DB;

class RegisterOrganizationController extends Controller
{
    /**
     * Register a new organization user
     *
     * Register a new organization user account in the system. This endpoint creates
     * a new user with the 'organization' role, along with their organization profile
     * information and location. Returns an authentication token upon successful registration.
     *
     * @group Authentication
     * @unauthenticated
     *
     * @bodyParam name string required The full name of the organization. Example: Helping Hands
     * @bodyParam email string required The email address for login (must be unique). Example: org@example.com
     * @bodyParam password string required The password (minimum 8 characters). Example: securePassword123
     * @bodyParam password_confirmation string required Password confirmation (must match password). Example: securePassword123
     * @bodyParam location_id integer required The ID of the organization's location. Example: 1
     * @bodyParam sector_id integer required The ID of the organization's sector. Example: 2
     * @bodyParam contact_email string required The contact email for the organization. Example: contact@org.com
     * @bodyParam website string optional The organization's website URL. Example: https://org.com
     * @bodyParam bio string optional A short description of the organization. Example: We help communities thrive.
     * @bodyParam avatar file optional Profile avatar image (jpg, png, gif, max 2MB).
     */
    public function store(StoreRegisterOrganizationRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name(),
                'email' => $request->email(),
                'password' => $request->password(),
                'organization_id' => null, // Will be set after organization creation
            ]);

            $user->assignRole(Role::organization->value);
            $organization = $user->organization()->create([
                'name' => $request->name(),
                'location_id' => $request->location(),
                'sector_id' => $request->sector(),
                'contact_email' => $request->contactEmail(),
                'website' => $request->website(),
                'bio' => $request->bio(),
            ]);

            $user->organization_id = $organization->id;
            $user->save();

            if ($request->avatar()) {
                $user->addMedia($request->avatar())
                    ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            }

            $accessToken = $user->createToken(config('app.name'))->plainTextToken;

            return AuthTokenResource::make(
                new AccessToken(accessToken: $accessToken, role: Role::organization),
            )->response()->setStatusCode(201);
        });
    }
}
