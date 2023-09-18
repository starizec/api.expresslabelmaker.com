<?php

namespace App\Services;

use App\Models\ApiError;
use App\Models\User;

class ErrorService
{
    static public function write($user_email, $error_status, $error_message, $request, $stack_trace, $log)
    {
        $user = User::where('email', $user_email)->first();

        $error = ApiError::create([
            "user_id" => isset($user->id) ? $user->id : null,
            "error_status" => $error_status,
            "error_message" => $error_message,
            "request" => json_encode($request->all()),
            "stack_trace" => $stack_trace,
            "log" => $log
        ]);

        return [
            "error_id" => $error->id,
            "error_details" => $error_message
        ];
    }
}
