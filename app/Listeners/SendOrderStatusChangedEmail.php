<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendOrderStatusChangedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order->load('user');
        if (! $order->user) {
            return;
        }
        $notify = config('shop.order_status_email_on', []);
        if (! in_array($order->status, $notify, true)) {
            return;
        }
        $st = OrderStatus::tryFrom($order->status);

        $orderUrl = URL::route('orders.show', $order);
        Mail::to($order->user->email)->send(new OrderStatusChangedMail(
            $order,
            $orderUrl,
            $st?->label() ?? ucfirst($order->status),
        ));
    }
}
