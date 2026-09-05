@extends('emails.layout', ['title' => 'Order confirmation'])

@section('content')
    <h1 style="font-size: 20px;">Thank you for your order</h1>
    <p>Order <strong>{{ $order->order_number }}</strong> has been received. Payment: COD (cash on delivery).</p>
    <p><strong>Total:</strong> {{ number_format((float) $order->total, 0, ',', '.') }} VND</p>
    <p><strong>Ship to:</strong><br>
        {{ $order->recipient_name }}<br>
        {{ $order->line1 }}@if($order->line2), {{ $order->line2 }}@endif<br>
        {{ $order->city }}, {{ $order->postal_code }}, {{ $order->country }}
    </p>
    <p><a href="{{ $orderUrl }}">View order</a></p>
@endsection
