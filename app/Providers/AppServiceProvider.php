<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Listeners\MergeSessionCartOnLogin;
use App\Listeners\SendOrderPlacedEmails;
use App\Listeners\SendOrderStatusChangedEmail;
use App\Listeners\SendWelcomeEmail;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(Registered::class, SendWelcomeEmail::class);
        Event::listen(Login::class, MergeSessionCartOnLogin::class);
        Event::listen(OrderPlaced::class, SendOrderPlacedEmails::class);
        Event::listen(OrderStatusChanged::class, SendOrderStatusChangedEmail::class);

        View::composer('layouts.shop', function ($view) {
            try {
                $cart = app(CartService::class)->currentCart();
                $cartItemCount = (int) $cart->items()->sum('quantity');
            } catch (\Throwable) {
                $cartItemCount = 0;
            }
            $view->with('cartItemCount', $cartItemCount);
        });
    }
}
