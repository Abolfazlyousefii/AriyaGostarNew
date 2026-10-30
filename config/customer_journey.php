<?php
return [
    // Fail closed by default. Set true after migrating and testing on staging.
    'enabled' => env('CUSTOMER_JOURNEY_ENABLED', false),
    'client_enabled' => env('CUSTOMER_JOURNEY_CLIENT_ENABLED', false),
    'retention_days' => (int) env('CUSTOMER_JOURNEY_RETENTION_DAYS', 30),
];
