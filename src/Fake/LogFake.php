<?php

declare(strict_types=1);

namespace AstralTesting\Fake;

use PHPUnit\Framework\Assert;

/**
 * Double de Logger (même API publique : debug/info/warning/error).
 */
final class LogFake
{
    /**
     * @var list<array{level: string, message: string, context: array<string, mixed>}>
     */
    private array $entries = [];

    /** @param array<string, mixed> $context */
    public function debug(string $message, array $context = []): void
    {
        $this->write('debug', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    public function reset(): void
    {
        $this->entries = [];
    }

    /**
     * @return list<array{level: string, message: string, context: array<string, mixed>}>
     */
    public function entries(?string $level = null): array
    {
        if ($level === null) {
            return $this->entries;
        }

        $level = strtolower($level);

        return array_values(array_filter(
            $this->entries,
            static fn (array $e): bool => $e['level'] === $level,
        ));
    }

    /**
     * @param string|(callable(array{level: string, message: string, context: array<string, mixed>}): bool) $message
     */
    public function assertLogged(string $level, string|callable $message): void
    {
        $level = strtolower($level);
        $matches = $this->entries($level);

        Assert::assertNotEmpty(
            $matches,
            "Failed asserting that a [{$level}] log entry was written.",
        );

        if (is_callable($message)) {
            foreach ($matches as $entry) {
                if ($message($entry)) {
                    Assert::assertTrue(true);

                    return;
                }
            }
            Assert::fail("Log [{$level}] was written but failed the callback assertion.");
        }

        foreach ($matches as $entry) {
            if (str_contains($entry['message'], $message)) {
                Assert::assertTrue(true);

                return;
            }
        }

        Assert::fail("Failed asserting that log [{$level}] contains [{$message}].");
    }

    public function assertNotLogged(string $level, string $needle): void
    {
        $level = strtolower($level);
        foreach ($this->entries($level) as $entry) {
            Assert::assertStringNotContainsString(
                $needle,
                $entry['message'],
                "Failed asserting that log [{$level}] does not contain [{$needle}].",
            );
        }
        Assert::assertTrue(true);
    }

    /** @param array<string, mixed> $context */
    private function write(string $level, string $message, array $context): void
    {
        $this->entries[] = [
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}
