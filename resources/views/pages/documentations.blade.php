@extends('layouts.app')

@section('title', $post->title)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="post-title">{{ $post->title }}</h1>
            <p class="post-content">{!! $post->content !!}</p>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Svi članci</h2>
            <ul>
                @foreach ($allPosts as $post)
                    <li><a href="{{ route('pages.posts', ['lang' => app()->getLocale(), 'slug' => $post->slug]) }}">{{ $post->title }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
