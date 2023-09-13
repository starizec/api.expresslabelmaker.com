<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;
use App\Models\Domain;
use App\Models\Licence;

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

            if (
                !User::where('email', $jsonData['user']['email'])->exists() ||
                !Domain::where('name', $jsonData['user']['domain'])->exists() ||
                !Licence::where('licence_uid', $jsonData['user']['licence'])->exists()
            ) {
                return response()->json([
                    "errors" => [
                        [
                            "error_id" => 1,
                            "error_details" => "Wrong email, domain, or licence."
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
