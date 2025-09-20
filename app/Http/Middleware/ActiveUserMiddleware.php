<?php

namespace App\Http\Middleware;

use App\Contracts\ResponseBuilder;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status === UserStatus::inactive) {
            $response = app(ResponseBuilder::class);
            $response->error(
                title: 'Account Inactive',
                detail: 'Your account has been deactivated. Please contact support for more information.',
                code: Response::HTTP_FORBIDDEN,
                indicator: 'DEACTIVATED',
            );

            return $response->build(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
