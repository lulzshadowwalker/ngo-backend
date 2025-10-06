<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ResponseBuilder;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AuthTokenResource;
use App\Support\AccessToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * User login
     *
     * Authenticate a user with email and password. Returns an access token upon successful authentication.
     *
     * @group Authentication
     *
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
            'data.relationships.deviceTokens.data.attributes.token' => 'nullable|string',
        ]);
        $email = $request->input('data.attributes.email');
        $password = $request->input('data.attributes.password');

        // Use the web guard for authentication attempt
        if (! auth('web')->attempt([
            'email' => $email,
            'password' => $password,
        ])) {
            //  TODO: We can handle the repsonse better
            throw new \Illuminate\Auth\AuthenticationException('Invalid credentials');
        }

        $user = auth('web')->user();

        if ($user->status === UserStatus::inactive) {
            $response = app(ResponseBuilder::class);
            $response->error(
                title: 'Account Inactive',
                detail: 'Your account has been deactivated. Please contact support for more information.',
                code: Response::HTTP_FORBIDDEN,
                indicator: 'DEACTIVATED',
            );

            return $response->build(Response::HTTP_FORBIDDEN);
        }

        if ($deviceToken = $request->input('data.relationships.deviceTokens.data.attributes.token')) {
            $user->deviceTokens()->firstOrCreate(['token' => $deviceToken]);
        }

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
