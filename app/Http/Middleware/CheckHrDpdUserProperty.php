<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Logger\ApiErrorLogger;

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
                ApiErrorLogger::apiError(
                    "900 - Missing DPD username or password.",
                    $request,
                    "900 - Missing DPD username or password.",
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                return response()->json([
                    "errors" => [
                        [
                            'error_message' => 'Missing DPD username or password.',
                            'error_code' => '900'
                        ]
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
                "errors" => [
                    [
                        'error_message' => 'Invalid request format. Expecting JSON.',
                        'error_code' => '804'
                    ]
                ]
            ], 400);
        }

        return $next($request);
    }
}
