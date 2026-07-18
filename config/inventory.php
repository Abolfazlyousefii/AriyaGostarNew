<?php

return [
    'enabled' => env('INVENTORY_INTEGRATION_ENABLED', false),
    'base_url' => env('INVENTORY_BASE_URL'),
    'shared_secret' => env('INVENTORY_SHARED_SECRET'),
    'connect_timeout' => env('INVENTORY_CONNECT_TIMEOUT', 5),
    'timeout' => env('INVENTORY_TIMEOUT', 15),
    'verify_ssl' => env('INVENTORY_VERIFY_SSL', true),
    'max_attempts' => env('INVENTORY_MAX_ATTEMPTS', 10),
    'allowed_ips' => array_filter(array_map('trim', explode(',', env('INVENTORY_ALLOWED_IPS', '')))),
    'timestamp_tolerance' => env('INVENTORY_TIMESTAMP_TOLERANCE', 300),
];
