<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    public function switchLang($lang)
    {
        session(['locale' => $lang]);
        
        // If redirect parameter is provided, use it
        if (request()->has('redirect')) {
            $redirectPath = request()->get('redirect');
            // Remove any existing language prefix from the redirect path
            $pathWithoutLang = preg_replace('/^[a-zA-Z]{2}\//', '', $redirectPath);
            return redirect('/' . $lang . '/' . $pathWithoutLang);
        }
        
        // Get the current URL path
        $currentPath = request()->path();
        
        // Remove the current language prefix if it exists
        $pathWithoutLang = preg_replace('/^[a-zA-Z]{2}\//', '', $currentPath);
        
        // If we're on the language switch route itself, redirect to home
        if ($pathWithoutLang === 'language/' . $lang) {
            return redirect('/' . $lang);
        }
        
        // If the path is empty after removing language prefix, it means we're on home
        if (empty($pathWithoutLang)) {
            return redirect('/' . $lang);
        }
        
        // Redirect to the same page with the new language prefix
        return redirect('/' . $lang . '/' . $pathWithoutLang);
    }
} 