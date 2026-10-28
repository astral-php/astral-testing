<?php

declare(strict_types=1);

namespace AstralTesting\Tests\Factory;

use AstralTesting\Tests\Fixtures\UserFactory;
use LogicException;
use PHPUnit\Framework\TestCase;

final class FactoryTest extends TestCase
{
    public function testMakeReturnsDefinition(): void
    {
        $attrs = UserFactory::new()->make();

        $this->assertSame('User 1', $attrs['name']);
        $this->assertSame('user1@example.test', $attrs['email']);
        $this->assertSame('user', $attrs['role']);
    }

    public function testMakeOverridesWin(): void
    {
        $attrs = UserFactory::new()->make(['email' => 'custom@example.test']);

        $this->assertSame('custom@example.test', $attrs['email']);
        $this->assertSame('User 1', $attrs['name']);
    }

    public function testStateAdmin(): void
    {
        $attrs = UserFactory::new()->admin()->make();

        $this->assertSame('admin', $attrs['role']);
    }

    public function testMakeMany(): void
    {
        $list = UserFactory::new()->count(3)->make();

        $this->assertCount(3, $list);
        $this->assertSame('user1@example.test', $list[0]['email']);
        $this->assertSame('user2@example.test', $list[1]['email']);
        $this->assertSame('user3@example.test', $list[2]['email']);
    }

    public function testCreateWithoutPersisterThrows(): void
    {
        $this->expectException(LogicException::class);
        UserFactory::new()->create();
    }

    public function testCreateWithCallback(): void
    {
        $store = [];

        $created = UserFactory::new()
            ->creating(static function (array $attrs) use (&$store): array {
                $attrs['id'] = count($store) + 1;
                $store[] = $attrs;

                return $attrs;
            })
            ->create(['name' => 'Ada']);

        $this->assertSame(1, $created['id']);
        $this->assertSame('Ada', $created['name']);
        $this->assertCount(1, $store);
    }

    public function testCreateMany(): void
    {
        $created = UserFactory::new()
            ->count(2)
            ->creating(static fn (array $attrs): array => $attrs + ['saved' => true])
            ->create();

        $this->assertCount(2, $created);
        $this->assertTrue($created[0]['saved']);
        $this->assertTrue($created[1]['saved']);
    }

    public function testCallableState(): void
    {
        $attrs = UserFactory::new()
            ->state(static fn (array $a): array => ['name' => strtoupper((string) $a['name'])])
            ->make();

        $this->assertSame('USER 1', $attrs['name']);
    }
}
