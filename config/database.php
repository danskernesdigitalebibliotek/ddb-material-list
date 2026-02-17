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
            'host' => env('MARIADB_HOST', env('DB_HOST', 'mariadb')),
            'port' => env('MARIADB_PORT', env('DB_PORT', '3306')),
            'database' => env('MARIADB_DATABASE', env('DB_DATABASE', 'lagoon')),
            'username' => env('MARIADB_USERNAME', env('DB_USERNAME', 'lagoon')),
            'password' => env('MARIADB_PASSWORD', env('DB_PASSWORD', 'lagoon')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
        ],

        // TODO use the MariaDB driver when we get to a Laravel version that
        // supports it.
        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('MARIADB_HOST', env('DB_HOST', 'mariadb')),
            'port' => env('MARIADB_PORT', env('DB_PORT', '3306')),
            'database' => env('MARIADB_DATABASE', env('DB_DATABASE', 'lagoon')),
            'username' => env('MARIADB_USERNAME', env('DB_USERNAME', 'lagoon')),
            'password' => env('MARIADB_PASSWORD', env('DB_PASSWORD', 'lagoon')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? \Pdo\Mysql::ATTR_SSL_CA : \PDO::MYSQL_ATTR_SSL_CA) =>
                    env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

    ],
    'migrations' => 'migrations',
];
