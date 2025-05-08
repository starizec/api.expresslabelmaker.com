@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ $post->translate()->title }}</h1>
            @if($post->cover_image)
                <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->translate()->title }}" class="img-fluid mb-4">
            @endif
            <div class="content">
                {!! $post->translate()->content !!}
            </div>
        </div>
    </div>
</div>
@endsection 