<?php

declare(strict_types=1);

namespace AstralTesting\Tests\Fixtures\App\Controllers;

use Core\Http\JsonResponse;
use Core\Http\Response;
use Core\Request;

final class EchoController
{
    public function __construct(private Request $request) {}

    public function store(): Response
    {
        return JsonResponse::make([
            'name' => (string) $this->request->input('name', ''),
        ]);
    }
}
