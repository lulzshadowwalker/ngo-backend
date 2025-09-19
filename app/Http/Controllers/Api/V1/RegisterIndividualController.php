<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRegisterIndividualRequest;
use App\Http\Resources\V1\AuthTokenResource;
use App\Models\User;
use App\Support\AccessToken;
use Illuminate\Support\Facades\DB;

class RegisterIndividualController extends Controller
{
    /**
     * Register a new individual user
     *
     * Register a new individual user account in the system. This endpoint creates
     * a new user with the 'individual' role, along with their profile information
     * and location. Returns an authentication token upon successful registration.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam name string required The full name of the individual. Example: John Doe
     * @bodyParam email string required The email address (must be unique). Example: john.doe@example.com
     * @bodyParam password string required The password (minimum 8 characters). Example: securePassword123
     * @bodyParam password_confirmation string required Password confirmation (must match password). Example: securePassword123
     * @bodyParam location_id integer required The ID of the user's location. Example: 1
     * @bodyParam avatar file optional Profile avatar image (jpg, png, gif, max 2MB).
     */
    public function store(StoreRegisterIndividualRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name(),
                'email' => $request->email(),
                'password' => $request->password(),
            ]);

            $user->assignRole(Role::individual->value);
            $individual = $user->individual()->create([
                'location_id' => $request->location(),
                'bio' => $request->bio(),
                'birthdate' => $request->birthdate(),
                'phone' => $request->phone(),
            ]);

            $individual->sectors()->attach($request->sectors());

            $individual->skills()->attach($request->skills());

            if ($request->avatar()) {
                $user->addMedia($request->avatar())
                    ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            }

            $accessToken = $user->createToken(config('app.name'))->plainTextToken;

            return AuthTokenResource::make(
                new AccessToken(accessToken: $accessToken, role: Role::individual),
            )->response()->setStatusCode(201);
        });
    }
}
