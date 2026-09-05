@extends('emails.layout', ['title' => 'Newsletter'])

@section('content')
    <h1 style="font-size: 20px;">You are subscribed</h1>
    <p>Thanks for joining our newsletter. You can unsubscribe any time using the link below.</p>
    <p><a href="{{ $unsubscribeUrl }}">Unsubscribe</a></p>
@endsection
