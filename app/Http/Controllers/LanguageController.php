<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    public function switchLang($lang)
    {
        Log::info('Switching language to: ' . $lang);
        session(['locale' => $lang]);
        Log::info('Session locale set to: ' . session('locale'));
        
        // Get the current URL path without the language prefix
        $currentPath = request()->path();
        $pathWithoutLang = preg_replace('/^[a-zA-Z]{2}\//', '', $currentPath);
        
        // If we're on the language switch route itself, redirect to home
        if ($pathWithoutLang === 'language/' . $lang) {
            return redirect('/' . $lang);
        }
        
        // Redirect to the same page with the new language prefix
        return redirect('/' . $lang . '/' . $pathWithoutLang);
    }
} 