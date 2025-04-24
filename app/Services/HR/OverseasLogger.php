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
        Log::channel('hr-overseas')->error('HR OVerseas API Error', [
            'user' => $user,
            'domain' => $domain,
            'status' => $status,
            'error' => $error,
            'request' => $request,
            'stack_trace' => $stack_trace,
            'log' => $log
        ]);
    }

    public static function usage($user, $domain, $request)
    {
        Log::channel('hr-overseas')->info('HR OVerseas Usage', [
            'user' => $user,
            'domain' => $domain,
            'request' => $request
        ]);
    }
}