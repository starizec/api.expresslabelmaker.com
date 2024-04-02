<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\UserService;
use App\Services\DomainService;
use App\Services\ErrorService;
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
            $jsonData = json_decode($request->getContent(), true); // Decode JSON into an associative array

            $validator = validator($jsonData['user'], [
                'email' => 'required|email',
                'domain' => 'required|string',
                'licence' => 'required|string'
            ]);
            return response()->json(["errors" => $jsonData]);
            if ($validator->fails()) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $jsonData['user']['email'],
                            400,
                            "Invalid data for email, domain, or licence.",
                            $request,
                            "namespace App\Http\Middleware\AuthenticateRequest@handle::" . __LINE__,
                            ""
                        )
                    ],
                ], 400);
            }

            $user_s = new UserService();
            $licence = $user_s->checkUserLicence(new UserClass($jsonData['user']['email'], DomainService::parseDomain($jsonData['user']['domain']), $jsonData['user']['licence']), $pl_no);
            
            if ($licence['status'] > 300) {
                return response()->json([
                    "errors" => [
                        ErrorService::write(
                            $jsonData['user']['email'],
                            400,
                            $licence['status'] . ' - ' . $licence['message'],
                            $request,
                            "namespace App\Http\Middleware\AuthenticateRequest@handle::" . __LINE__,
                            ""
                        )
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
