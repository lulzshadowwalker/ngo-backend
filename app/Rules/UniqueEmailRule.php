<?php

namespace App\Rules;

use App\Contracts\ResponseBuilder;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UniqueEmailRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (User::where('email', $value)->exists()) {
            throw new HttpResponseException(
                app(ResponseBuilder::class)
                    ->error(
                        title: 'Email already exists',
                        detail: 'An account with this email address already exists',
                        code: Response::HTTP_CONFLICT,
                        indicator: 'EMAIL_ALREADY_EXISTS'
                    )->build(Response::HTTP_CONFLICT)
            );
        }
    }
}
