<?php

namespace App\Enums;

enum CouponKind: string
{
    case Product = 'product';
    case Shipping = 'shipping';
}
