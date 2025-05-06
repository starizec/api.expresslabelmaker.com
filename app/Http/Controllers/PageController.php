<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::whereHas('translations', function($query) use ($slug) {
            $query->where('slug', $slug);
        })->with('translations')->first();
        
        if (!$page) {
            abort(404);
        }
        
        return view('frontend.pages.index', compact('page'));
    }
}
