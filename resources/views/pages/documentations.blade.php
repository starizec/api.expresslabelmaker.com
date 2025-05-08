@extends('layouts.app')

@section('title', $post->title)

@section('content')
<div class="container mb-5">
    <div class="row">
        <div class="col-md-4 mt-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4">{{ __('documentations.all_documentations') }}</h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach ($allPosts as $sidebarPost)
                            <li class="mb-2">
                                <a href="{{ route('pages.documentations', ['lang' => app()->getLocale(), 'slug' => $sidebarPost->slug]) }}">{{ $sidebarPost->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <h1 class="post-title">{{ $post->title }}</h1>
            <p class="post-content">{!! $post->content !!}</p>
        </div>
    </div>
</div>
@endsection
