<?php

declare(strict_types=1);

function app_config(?string $section = null): array
{
    static $config;

    if ($config === null) {
        $config = require __DIR__ . '/config.php';
        date_default_timezone_set($config['app']['timezone'] ?? 'UTC');
    }

    if ($section === null) {
        return $config;
    }

    return $config[$section] ?? [];
}
