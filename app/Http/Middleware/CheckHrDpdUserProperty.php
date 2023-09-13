<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHrDpdUserProperty
{
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
                        [
                            "error_id" => 1,
                            "error_details" => "Missing DPD username or password."
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
