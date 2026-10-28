<?php

declare(strict_types=1);

namespace AstralTesting\Tests;

use AstralTesting\Package;
use PHPUnit\Framework\TestCase;

final class PackageTest extends TestCase
{
    public function testNameAndVersion(): void
    {
        $this->assertSame('astral-php/astral-testing', Package::name());
        $this->assertSame('0.1.0', Package::version());
    }
}
