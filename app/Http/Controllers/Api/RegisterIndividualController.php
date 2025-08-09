<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterIndividualRequest;
use App\Http\Resources\AuthTokenResource;
use App\Models\User;
use App\Support\AccessToken;
use Illuminate\Support\Facades\DB;

class RegisterIndividualController extends Controller
{
    public function store(StoreRegisterIndividualRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name(),
                'email' => $request->email(),
                'password' => $request->password(),
            ]);

            $user->assignRole(Role::individual->value);
            $user->individual()->create([
                'location_id' => $request->location(),
            ]);

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
