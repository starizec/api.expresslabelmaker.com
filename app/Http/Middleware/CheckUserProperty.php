<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserProperty
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson()) {
            $jsonData = json_decode($request->getContent());

            if (
                !isset($jsonData->user) ||
                (!isset($jsonData->user->domain) || !isset($jsonData->user->email) || !isset($jsonData->user->licence)) ||
                (!is_string($jsonData->user->domain) || !is_string($jsonData->user->email) || !is_string($jsonData->user->licence))
            ) {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 1,
                            "error_details" => "Missing user properties."
                        ]
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
