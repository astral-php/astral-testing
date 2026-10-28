<?php

declare(strict_types=1);

use Core\Providers\DatabaseServiceProvider;
use Core\Providers\FrameworkServiceProvider;

return [
    FrameworkServiceProvider::class,
    DatabaseServiceProvider::class,
];
