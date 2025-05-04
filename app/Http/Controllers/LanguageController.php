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
        return redirect()->back();
    }
} 