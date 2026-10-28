<?php

declare(strict_types=1);

namespace AstralTesting\Fake;

use PHPUnit\Framework\Assert;

/**
 * Double d’envoi mail (même signature publique que Core\Mailer::send).
 *
 * À injecter à la place du Mailer dans les tests (container / constructeur).
 */
final class MailFake
{
    /**
     * @var list<array{to: string, subject: string, html: string}>
     */
    private array $sent = [];

    public function send(string $to, string $subject, string $html): void
    {
        $this->sent[] = [
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html,
        ];
    }

    public function reset(): void
    {
        $this->sent = [];
    }

    /**
     * @return list<array{to: string, subject: string, html: string}>
     */
    public function sent(): array
    {
        return $this->sent;
    }

    /**
     * @param (callable(array{to: string, subject: string, html: string}): bool)|null $callback
     */
    public function assertSent(?callable $callback = null): void
    {
        Assert::assertNotEmpty($this->sent, 'Failed asserting that a mail was sent.');

        if ($callback === null) {
            return;
        }

        foreach ($this->sent as $mail) {
            if ($callback($mail)) {
                Assert::assertTrue(true);

                return;
            }
        }

        Assert::fail('A mail was sent but failed the callback assertion.');
    }

    /**
     * @param (callable(array{to: string, subject: string, html: string}): bool)|null $callback
     */
    public function assertSentTo(string $to, ?callable $callback = null): void
    {
        $matches = array_values(array_filter(
            $this->sent,
            static fn (array $m): bool => $m['to'] === $to,
        ));

        Assert::assertNotEmpty($matches, "Failed asserting that a mail was sent to [{$to}].");

        if ($callback === null) {
            return;
        }

        foreach ($matches as $mail) {
            if ($callback($mail)) {
                Assert::assertTrue(true);

                return;
            }
        }

        Assert::fail("Mail to [{$to}] was sent but failed the callback assertion.");
    }

    public function assertNothingSent(): void
    {
        Assert::assertEmpty($this->sent, 'Failed asserting that no mail was sent.');
    }

    public function assertSentCount(int $count): void
    {
        Assert::assertCount($count, $this->sent);
    }
}
