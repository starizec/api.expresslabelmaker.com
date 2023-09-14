<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\UserService;
use App\Services\DomainService;
use App\Classes\UserClass;

class AuthenticateRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson()) {
            $jsonData = json_decode($request->getContent(), true); // Decode JSON into an associative array

            $validator = validator($jsonData['user'], [
                'email' => 'required|email',
                'domain' => 'required|string',
                'licence' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 1,
                            "error_details" => "Invalid data for email, domain, or licence."
                        ]
                    ],
                ], 400);
            }

            $user_s = new UserService();
            $licence = $user_s->checkUserLicence(new UserClass($jsonData['user']['email'], DomainService::parseDomain($jsonData['user']['domain']), $jsonData['user']['licence']));

            if ($licence['status'] > 300) {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 1,
                            "error_details" => $licence['status'] . ' - ' . $licence['message']
                        ]
                    ],
                ], $licence['status']);
            }
        } else {
            return response()->json([
                "error" => "Invalid request format. Expecting JSON."
            ], 400);
        }
        return $next($request);
    }
}
