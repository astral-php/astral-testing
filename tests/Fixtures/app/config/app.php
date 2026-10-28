<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>
 */
return [
    'name'     => 'Astral Testing Fixture',
    'version'  => '0.1.0-dev',
    'env'      => $_ENV['APP_ENV'] ?? 'testing',
    'debug'    => true,
    'timezone' => 'UTC',
    'charset'  => 'UTF-8',
    'base_url' => '',
    'mail'     => [
        'driver' => 'mail',
        'from'   => 'test@localhost',
    ],
];
