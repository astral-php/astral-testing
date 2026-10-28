<?php

declare(strict_types=1);

namespace AstralTesting\Tests\Fixtures\App\Controllers;

use Core\Http\Response;

final class HomeController
{
    public function index(): Response
    {
        return Response::html('fixture-home-ok', 200);
    }
}
