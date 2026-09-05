@extends('emails.layout', ['title' => 'New review'])

@section('content')
    <h1 style="font-size: 20px;">New product review to moderate</h1>
    <p>Product: {{ $review->product->name }} — rating {{ $review->rating }}/5</p>
    <p><a href="{{ $adminUrl }}">Review in admin</a></p>
@endsection
