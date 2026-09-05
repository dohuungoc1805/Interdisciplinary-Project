@extends('emails.layout', ['title' => 'Welcome'])

@section('content')
    <h1 style="font-size: 20px;">Welcome, {{ $user->name }}!</h1>
    <p>Your account is ready. Start browsing our collection.</p>
    <p><a href="{{ $shopUrl }}">Visit the shop</a></p>
@endsection
