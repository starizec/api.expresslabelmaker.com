<?php

namespace App\Services\Logger;

use Illuminate\Support\Facades\Log;

class ApiErrorLogger
{
    public static function apiError($courier, $message, $request, $error, $stack_trace)
    {
        Log::channel('api-error')->error($courier . ' - ' . $message, [
            'stack_trace' => $stack_trace,
            'request' => $request->all(),
            'error' => $error
        ]);
    }
}