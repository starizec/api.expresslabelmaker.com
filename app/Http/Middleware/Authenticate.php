<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // Get the language from session or use default
            $lang = session('locale', 'hr');
            
            // Ensure the language is valid (only allow 'en' or 'hr')
            if (!in_array($lang, ['en', 'hr'])) {
                $lang = 'hr';
            }
            
            return route('login', ['lang' => $lang]);
        }
    }
}
