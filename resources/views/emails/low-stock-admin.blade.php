@extends('emails.layout', ['title' => 'Low stock'])

@section('content')
    <h1 style="font-size: 20px;">Low stock alert</h1>
    <p>These variants are at or below the threshold ({{ $threshold }}):</p>
    <ul>
        @foreach($lines as $line)
            <li>{{ $line }}</li>
        @endforeach
    </ul>
@endsection
