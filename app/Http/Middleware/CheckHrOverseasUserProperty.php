<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Logger\ApiErrorLogger;

class CheckHrOverseasUserProperty
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson()) {
            $jsonData = json_decode($request->getContent());

            if (
                !isset($jsonData->user) || (!isset($jsonData->user->apiKey) || (!is_string($jsonData->user->apiKey)))
            ) {
                ApiErrorLogger::apiError(
                    "901 - Missing Overseas Api key.",
                    $request,
                    "901 - Missing Overseas Api key.",
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                return response()->json([
                    "errors" => [
                        [
                            'error_message' => 'Missing Overseas Api key.',
                            'error_code' => '901'
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
