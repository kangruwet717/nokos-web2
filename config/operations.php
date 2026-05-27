<?php

return [
    'provider_low_balance_threshold' => (float) env('PROVIDER_LOW_BALANCE_THRESHOLD', 5),
    'pending_invoice_overdue_minutes' => (int) env('OPS_PENDING_INVOICE_OVERDUE_MINUTES', 30),
    'webhook_error_window_minutes' => (int) env('OPS_WEBHOOK_ERROR_WINDOW_MINUTES', 60),
    'webhook_error_threshold' => (int) env('OPS_WEBHOOK_ERROR_THRESHOLD', 3),
];
