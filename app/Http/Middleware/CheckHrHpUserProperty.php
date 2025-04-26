<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ErrorService;
class CheckHrHpUserProperty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson()) {
            $jsonData = json_decode($request->getContent());

            if (
                !isset($jsonData->user) ||
                (!isset($jsonData->user->username) || !isset($jsonData->user->password)) ||
                (!is_string($jsonData->user->username) || !is_string($jsonData->user->password))
            ) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $jsonData->user->apiKey,
                            400,
                            "Missing HP username or password.",
                            $request,
                            "namespace App\Http\Middleware\CheckHrHpUserProperty@handle::" . __LINE__,
                            ""
                        )
                    ],
                ], 400);
            }
        } else {
            return response()->json([
                "error" => "Invalid request format. Expecting JSON."
            ], 400);
        }

        return $next($request);

    }
}
