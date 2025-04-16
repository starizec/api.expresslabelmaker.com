<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ErrorService;

class CheckHrOverseasUserProperty
{
        public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson()) {
            $jsonData = json_decode($request->getContent());

            if (
                !isset($jsonData->user) || (!isset($jsonData->user->apiKey) || (!is_string($jsonData->user->apiKey)))
            ) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $jsonData->user->apiKey,
                            400,
                            "Missing Overseas Api key.",
                            $request,
                            "namespace App\Http\Middleware\CheckHrOverseasUserProperty@handle::" . __LINE__,
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
