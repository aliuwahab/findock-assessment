<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Address Validation Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default address validation driver that will be
    | used by the address validation manager.
    |
    */

    'default_driver' => env('ADDRESS_VALIDATION_DRIVER', 'geoapify'),

    /*
    |--------------------------------------------------------------------------
    | Address Validation Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the address validation drivers for your application.
    |
    */

    'drivers' => [
        'geoapify' => [
            'api_key' => env('GEOAPIFY_API_KEY'),
            'base_url' => env('GEOAPIFY_BASE_URL', 'https://api.geoapify.com/v1'),
            'timeout' => env('GEOAPIFY_TIMEOUT', 10),
        ],

        // Future providers can be added here
        // 'google' => [
        //     'api_key' => env('GOOGLE_MAPS_API_KEY'),
        //     'base_url' => 'https://maps.googleapis.com/maps/api',
        //     'timeout' => 10,
        // ],
        //
        // 'here' => [
        //     'api_key' => env('HERE_API_KEY'),
        //     'base_url' => 'https://geocode.search.hereapi.com/v1',
        //     'timeout' => 10,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for validation results.
    |
    */

    'cache' => [
        'enabled' => env('ADDRESS_VALIDATION_CACHE_ENABLED', true),
        'ttl' => env('ADDRESS_VALIDATION_CACHE_TTL', 86400), // 24 hours in seconds
    ],
];
