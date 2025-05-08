<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function download()
    {
        $post = Post::whereHas('translations', function($query) {
            $query->where('slug', 'preuzmi-plugin');
        })->with('translations')->first();

        return view('pages.donwload', compact('post'));
    }
}
