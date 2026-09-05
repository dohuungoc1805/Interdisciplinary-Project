<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeSessionCartOnLogin
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function handle(Login $event): void
    {
        $this->cartService->mergeGuestCartToUser($event->user);
    }
}
