<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function show($slug)
    {
        $post = Post::whereHas('translations', function($query) use ($slug) {
            $query->where('slug', $slug);
        })->with('translations')->first();
        
        if (!$post) {
            abort(404);
        }
        
        return view('pages.posts', compact('post'));
    }
}
