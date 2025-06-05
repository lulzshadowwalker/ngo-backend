<?php

namespace App\Http\Middleware;

use App\Contracts\ResponseBuilder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    public function __construct(protected ResponseBuilder $response)
    {
        //
    }

    public function handle(Request $request, Closure $next): Response
    {
        $language = $request->route("language");
        $supported = config("app.supported_locales");

        if (!in_array($language, $supported)) {
            return $this->response
                ->error(
                    title: "Language not supported",
                    detail: "The language '$language' is not supported.",
                    code: HttpResponse::HTTP_NOT_FOUND
                )
                ->build(HttpResponse::HTTP_NOT_FOUND);
        }

        app()->setLocale($language);

        return $next($request);
    }
}
