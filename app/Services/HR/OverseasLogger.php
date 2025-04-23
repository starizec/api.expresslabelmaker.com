<?php

namespace App\Services\HR;

use Illuminate\Support\Facades\Log;

class OverseasLogger
{
    public static function error($message, $context = [])
    {
        Log::channel('hr-overseas')->error($message, $context);
    }

    public static function apiError($user, $domain, $status, $error, $request, $stack_trace, $log)
    {
        self::error('Overseas API Error', [
            'user' => $user,
            'domain' => $domain,
            'status' => $status,
            'error' => $error,
            'request' => $request,
            'stack_trace' => $stack_trace,
            'log' => $log
        ]);
    }

    public static function validationError($error, $parcel)
    {
        self::error('Overseas Validation Error', [
            'error' => $error,
            'parcel' => $parcel
        ]);
    }
} 