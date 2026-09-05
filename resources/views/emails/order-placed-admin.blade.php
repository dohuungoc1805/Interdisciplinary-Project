@extends('emails.layout', ['title' => 'New order'])

@section('content')
    <h1 style="font-size: 20px;">New order received</h1>
    <p><strong>{{ $order->order_number }}</strong> &mdash; {{ $order->user->email ?? '' }}</p>
    <p>Total: {{ number_format((float) $order->total, 0, ',', '.') }} VND</p>
    <p><a href="{{ $adminUrl }}">Open in admin</a></p>
@endsection
