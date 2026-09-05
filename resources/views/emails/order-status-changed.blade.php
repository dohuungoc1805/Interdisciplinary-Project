@extends('emails.layout', ['title' => 'Order update'])

@section('content')
    <h1 style="font-size: 20px;">Your order was updated</h1>
    <p>Order <strong>{{ $order->order_number }}</strong> is now: <strong>{{ $statusLabel }}</strong></p>
    @if($order->tracking_number)
        <p>Tracking: {{ $order->tracking_number }}</p>
    @endif
    <p><a href="{{ $orderUrl }}">View order</a></p>
@endsection
