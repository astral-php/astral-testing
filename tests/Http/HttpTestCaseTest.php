<?php

declare(strict_types=1);

namespace AstralTesting\Tests\Http;

use AstralTesting\Http\HttpTestCase;
use Core\Exception\CsrfException;
use Core\Exception\NotFoundException;

final class HttpTestCaseTest extends HttpTestCase
{
    protected function applicationPath(): string
    {
        return dirname(__DIR__) . '/Fixtures/app';
    }

    public function testGetHomeReturns200(): void
    {
        $response = $this->get('/');
        $this->assertStatus($response, 200);
        $this->assertSee($response, 'fixture-home-ok');
    }

    public function testPostWithoutCsrfFails(): void
    {
        $this->expectException(CsrfException::class);
        $this->post('/echo', ['name' => 'Ada']);
    }

    public function testPostWithCsrfSucceeds(): void
    {
        $response = $this->post('/echo', $this->withCsrf(['name' => 'Ada']));
        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Ada');
    }

    public function testWithSessionPersistsAcrossRequest(): void
    {
        $this->withSession(['demo_key' => 'demo_value']);
        $this->get('/');
        $this->assertSame('demo_value', $this->session()->get('demo_key'));
    }

    public function testUnknownRouteThrows(): void
    {
        $this->expectException(NotFoundException::class);
        $this->get('/missing-route');
    }
}
