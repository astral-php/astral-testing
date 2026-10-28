<?php

declare(strict_types=1);

use AstralTesting\Tests\Fixtures\App\Controllers\EchoController;
use AstralTesting\Tests\Fixtures\App\Controllers\HomeController;
use Core\Middleware\CsrfMiddleware;
use Core\Router;

return static function (Router $router): void {
    $router->get('/', HomeController::class, 'index');

    $router->group('', static function (Router $r): void {
        $r->post('/echo', EchoController::class, 'store');
    }, [CsrfMiddleware::class]);
};
