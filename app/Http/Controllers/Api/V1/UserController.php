<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

use App\Services\ErrorService;

class UserController extends Controller
{
    public function create(Request $request)
    {
        $requestBody = $request->getContent();
        $data = json_decode($requestBody);

        if (!User::where('email', $data->email)->exists()) {
            User::create([
                'email' => $data->email,
                'wp_user_id' => $data->wp_user_id
            ]);

            return response()->json([
                "data" => "User created",
            ], 201);
        } else {
            return response()->json([
                "errors" => [
                    ErrorService::write("", 403, "User already exist.", $request, "App\Http\Controllers\Api\V1\UserController@create" . __LINE__, ''),
                ],
            ], 403);
        }
    }
}
