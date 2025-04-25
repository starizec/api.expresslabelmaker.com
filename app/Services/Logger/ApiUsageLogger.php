<?php

namespace App\Services\Logger;

use Illuminate\Support\Facades\Log;

class ApiUsageLogger
{
    public static function apiUsage($courier, $domain, $request)
    {
        Log::channel('api-usage')->info($courier . ' - ' . $domain, [
            'request' => $request->all()
        ]);
    }
}