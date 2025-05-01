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
            'method' => is_string($request) ? $request : ($request ? $request->method() : 'Unknown'),
            'url' => is_string($request) ? $request : ($request ? $request->fullUrl() : 'Unknown'),
            'headers' => is_string($request) ? 'Unknown' : ($request ? $request->headers->all() : 'Unknown'),
            'body' => is_string($request) ? 'Unknown' : ($request ? $request->json()->all() : 'Unknown'), // clean parsed JSON
        ];

        Log::channel('api-error')->error($message, $logData);
    }
}