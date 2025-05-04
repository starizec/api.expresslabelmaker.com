<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\UserService;
use App\Services\DomainService;
use App\Services\Logger\ApiErrorLogger;
use App\Classes\UserClass;

class CheckUserLicence
{
    public function handle(Request $request, Closure $next): Response
    {
        $pl_no = 1;

        if (isset($request->parcels)) {
            $pl_no = count($request->parcels);
        }

        if ($request->isJson()) {
            $jsonData = json_decode($request->getContent(), true);

            $validator = validator($jsonData['user'], [
                'email' => 'required|email',
                'domain' => 'required|string',
                'licence' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                ApiErrorLogger::apiError(
                    "Invalid data for email, domain, or licence.",
                    $request,
                    "Invalid data for email, domain, or licence.",
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                return response()->json([
                    "errors" => [
                        [
                            'error_message' => 'Invalid data for email, domain, or licence.',
                            'error_code' => '804'
                        ]
                    ],
                ], 400);
            }

            $user_s = new UserService();
            $licence = $user_s->checkUserLicence(new UserClass($jsonData['user']['email'], DomainService::parseDomain($jsonData['user']['domain']), $jsonData['user']['licence']), $pl_no);

            if ($licence['status'] > 300) {
                ApiErrorLogger::apiError(
                    $licence['error_code'] . ' - ' . $licence['message'],
                    $request,
                    $licence['error_code'] . ' - ' . $licence['message'],
                    __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
                );

                return response()->json([
                    "errors" => [
                        [
                            'error_message' => $licence['message'],
                            'error_code' => $licence['error_code']
                        ],
                    ],
                ], $licence['status']);
            }
        } else {
            ApiErrorLogger::apiError(
                "804 - Invalid request format. Expecting JSON.",
                $request,
                "804 - Invalid request format. Expecting JSON.",
                __CLASS__ . '@' . __FUNCTION__ . '::' . __LINE__
            );

            return response()->json([
                "error" => "Invalid request format. Expecting JSON.",
                "error_code" => "804"
            ], 400);
        }
        return $next($request);
    }
}
