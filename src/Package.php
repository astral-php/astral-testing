<?php

declare(strict_types=1);

namespace AstralTesting;

/**
 * Métadonnées du package (smoke autoload Phase 1).
 */
final class Package
{
    public const NAME = 'astral-php/astral-testing';

    public const VERSION = '0.1.0';

    public static function name(): string
    {
        return self::NAME;
    }

    public static function version(): string
    {
        return self::VERSION;
    }
}
