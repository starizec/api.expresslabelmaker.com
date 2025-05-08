<?php

namespace App\Http\Controllers;

use App\Models\PostTranslation;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostType;

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
        })->where('status', 'published')->with('translations')->first();
        
        if (!$post) {
            abort(404);
        }
        
        return view('pages.posts', compact('post'));
    }

    public function documentationPost(string $lang, string $slug){
        $allPosts = Post::whereHas('translations', function($query) {
            $query->where('post_type_id', PostType::where('name', 'dokumentacija')->first()->id);
        })->with('translations')->get();

        $post = Post::whereHas('translations', function($query) use ($slug) {
            $query->where('slug', $slug);
        })->where('status', 'published')->with('translations')->first();

        if (!$post) {
            abort(404);
        }
        
        return view('pages.documentations', compact('post', 'allPosts'));
    }
}
