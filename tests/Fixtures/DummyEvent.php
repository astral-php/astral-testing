<?php

declare(strict_types=1);

namespace AstralTesting\Tests\Fixtures;

use Core\Events\EventInterface;

final class DummyEvent implements EventInterface
{
    public function __construct(public readonly string $name) {}
}
