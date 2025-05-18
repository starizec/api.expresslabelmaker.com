<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    protected $availableLocales = ['en', 'hr'];

    public function handle(Request $request, Closure $next)
    {
        // Skip locale setting for admin and Livewire update routes
        if (
            $request->is('admin/*') ||
            $request->is('livewire/*')
        ) {
            return $next($request);
        }

        if (session()->has('locale')) {
            $locale = session('locale');
            if (in_array($locale, $this->availableLocales)) {
                App::setLocale($locale);
            } else {
                App::setLocale(config('app.locale'));
            }
        }

        return $next($request);
    }
} 