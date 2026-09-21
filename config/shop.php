<?php

return [
    'default_shipping' => (float) env('SHOP_DEFAULT_SHIPPING', 30000),
    'free_shipping_threshold' => (float) env('SHOP_FREE_SHIPPING_THRESHOLD', 500000),
    'admin_notify_emails' => array_filter(array_map('trim', explode(',', (string) env('ADMIN_NOTIFY_EMAILS', '')))),
    'low_stock_threshold' => (int) env('ADMIN_LOW_STOCK_THRESHOLD', 5),
    'order_status_email_on' => ['shipping', 'completed', 'cancelled'],
    'bank_transfer' => [
        'bank_code' => env('BANK_TRANSFER_BANK_CODE'),
        'bank_name' => env('BANK_TRANSFER_BANK_NAME'),
        'account_number' => env('BANK_TRANSFER_ACCOUNT_NUMBER'),
        'account_name' => env('BANK_TRANSFER_ACCOUNT_NAME'),
    ],
];
