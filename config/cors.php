<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // In your CORS config:
    'allowed_origins' => ['*', 'http://192.168.x.x:3000', 'https://192.168.x.x:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Auth-Token', 'Origin', 'Authorization', 'X-Requested-With', 'Accept'],

    'exposed_headers' => [''],

    'max_age' => 86400,  // 24 hours

    'supports_credentials' => true,  // Set to true if using cookies/sessions
];
