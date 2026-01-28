<?php

/**
 * Extra configuration added to handle SSL database connections.
 */
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => env('DB_PREFIX', ''),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'database'),
            'username' => env('DB_USERNAME', 'laravel'),
            'password' => env('DB_PASSWORD', 'password'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
        ],
    ],
    'migrations' => 'migrations',
];
