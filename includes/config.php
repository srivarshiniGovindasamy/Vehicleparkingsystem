<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'vehicle_parking_system',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'timezone' => 'Asia/Calcutta',
        'currency' => 'INR',
        'default_parking_rate_per_hour' => 20,
    ],
];
