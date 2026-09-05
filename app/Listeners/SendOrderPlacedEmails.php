<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderPlacedAdminMail;
use App\Mail\OrderPlacedCustomerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendOrderPlacedEmails implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->load(['user', 'items']);
        $user = $order->user;
        if (! $user) {
            return;
        }
        $orderUrl = URL::route('orders.show', $order);
        Mail::to($user->email)->send(new OrderPlacedCustomerMail($order, $orderUrl));

        $adminUrl = URL::route('admin.orders.show', $order);
        foreach (config('shop.admin_notify_emails', []) as $email) {
            if ($email) {
                Mail::to($email)->send(new OrderPlacedAdminMail($order, $adminUrl));
            }
        }
    }
}
