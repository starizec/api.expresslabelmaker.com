<?php

namespace App\Services\Logger;

use Illuminate\Support\Facades\Log;

class ApiErrorLogger
{
    public static function apiError($message, $request, $error, $stack_trace)
    {
        $logData = [
            'stack_trace' => $stack_trace,
            'error' => $error,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'body' => $request->json()->all(), // clean parsed JSON
        ];

        Log::channel('api-error')->error($message, $logData);
    }
}