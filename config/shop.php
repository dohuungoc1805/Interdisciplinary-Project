<?php

return [
    'default_shipping' => (float) env('SHOP_DEFAULT_SHIPPING', 30000),
    'admin_notify_emails' => array_filter(array_map('trim', explode(',', (string) env('ADMIN_NOTIFY_EMAILS', '')))),
    'low_stock_threshold' => (int) env('ADMIN_LOW_STOCK_THRESHOLD', 5),
    'order_status_email_on' => ['shipping', 'completed', 'cancelled'],
];
