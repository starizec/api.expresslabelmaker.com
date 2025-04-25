<?php

namespace App\Services\Logger;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiUsageLogger
{
    public static function apiUsage($message, Request $request)
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'body' => $request->json()->all(), // clean parsed JSON
        ];

        Log::channel('api-usage')->info($message, [
            'request' => $request
        ]);
    }
}