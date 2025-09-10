<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthTokenResource;
use App\Support\AccessToken;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * User login
     * 
     * Authenticate a user with email and password. Returns an access token upon successful authentication.
     *
     * @group Authentication
     * @unauthenticated
     * 
     * @bodyParam data.attributes.email string required The user's email address. Example: john.doe@example.com
     * @bodyParam data.attributes.password string required The user's password (minimum 8 characters). Example: securePassword123
     */
    public function store(Request $request)
    {
        $request->validate([
            'data.attributes.email' => 'required|email',
            'data.attributes.password' => 'required|string|min:8',
        ]);
        $email = $request->input('data.attributes.email');
        $password = $request->input('data.attributes.password');

        // Use the web guard for authentication attempt
        if (!auth('web')->attempt([
            'email' => $email,
            'password' => $password,
        ])) {
            //  TODO: We can handle the repsonse better
            throw new \Illuminate\Auth\AuthenticationException('Invalid credentials');
        }

        $user = auth('web')->user();

        $accessToken = $user->createToken(config('app.name'))->plainTextToken;

        return AuthTokenResource::make(
            new AccessToken(
                accessToken: $accessToken,
                role: $user->isIndividual ? Role::individual : Role::organization,
            ),
        )->response()
            ->setStatusCode(200);
    }
}
