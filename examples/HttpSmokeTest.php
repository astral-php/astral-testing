<?php

declare(strict_types=1);

namespace App\Tests\Http;

use AstralTesting\Http\HttpTestCase;

/**
 * Exemple de smoke HTTP — à copier sous tests/Http/ de ton app.
 */
final class HttpSmokeTest extends HttpTestCase
{
    protected function applicationPath(): string
    {
        return dirname(__DIR__, 2);
    }

    public function testHomeReturnsOk(): void
    {
        $response = $this->get('/');
        $this->assertStatus($response, 200);
    }

    public function testPostWithCsrf(): void
    {
        // Adapter l’URI et les champs à ton app
        $response = $this->post('/example', $this->withCsrf([
            'name' => 'Ada',
        ]));

        $this->assertContains($response->getStatus(), [200, 302]);
    }
}
