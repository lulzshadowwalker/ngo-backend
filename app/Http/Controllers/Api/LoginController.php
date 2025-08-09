<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthTokenResource;
use App\Support\AccessToken;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'data.attributes.email' => 'required|email',
            'data.attributes.password' => 'required|string|min:8',
        ]);
        $email = $request->input('data.attributes.email');
        $password = $request->input('data.attributes.password');

        if (!auth()->attempt([
            'email' => $email,
            'password' => $password,
        ])) {
            //  TODO: We can handle the repsonse better
            throw new \Illuminate\Auth\AuthenticationException('Invalid credentials');
        }

        $user = auth()->user();

        $accessToken = auth()->user()->createToken(config('app.name'))->plainTextToken;

        return AuthTokenResource::make(
            new AccessToken(
                accessToken: $accessToken,
                role: $user->isIndividual ? Role::individual : Role::organization,
            ),
        )->response()
            ->setStatusCode(200);
    }
}
