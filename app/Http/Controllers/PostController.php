<?php

namespace App\Http\Controllers;

use App\Models\PostTranslation;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function show(string $lang, string $slug)
    {
        $post = Post::whereHas('translations', function($query) use ($slug) {
            $query->where('slug', $slug);
        })->with('translations')->first();
        
        if (!$post) {
            abort(404);
        }
        
        return view('pages.posts', compact('post'));
    }

    public function legalPost(string $lang, string $slug)
    {
        $post = Post::whereHas('translations', function($query) use ($slug) {
            $query->where('slug', $slug);
        })->with('translations')->first();
        
        if (!$post) {
            abort(404);
        }
        
        return view('pages.posts', compact('post'));
    }

    public function documentationPost(string $lang, string $slug){
        // Get all posts of type 'dokumentacija'
        $allPosts = Post::whereHas('translations', function($query) {
            $query->where('post_type_id', 2);
        })->with('translations')->get();

        // Get single post by slug
        $post = Post::whereHas('translations', function($query) use ($slug) {
            $query->where('slug', $slug);
        })->with('translations')->first();
        
        if (!$post) {
            abort(404);
        }
        
        return view('pages.documentations', compact('post', 'allPosts'));
    }
}
