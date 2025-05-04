<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Logger\ApiErrorLogger;

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
                ApiErrorLogger::apiError(
                    "806 - Missing user properties.",
                    $request,
                    "806 - Missing user properties.",
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                return response()->json([
                    "errors" => [
                        [
                            'error_message' => "Missing user properties.",
                            'error_code' => "806"
                        ],
                    ],
                ], 400);
            }
        } else {
            ApiErrorLogger::apiError(
                "804 - Invalid request format. Expecting JSON.",
                $request,
                "804 - Invalid request format. Expecting JSON.",
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "error" => "804 - Invalid request format. Expecting JSON.",
                "error_code" => "804"
            ], 400);
        }

        return $next($request);
    }
}
