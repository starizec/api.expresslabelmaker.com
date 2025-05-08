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

@endsection
