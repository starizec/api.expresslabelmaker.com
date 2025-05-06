@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="post-title">{{ $page->title }}</h1>

        <div class="post-content">
            {!! $page->content !!}
        </div>
    </div>
@endsection
