<?php

declare(strict_types=1);

namespace AstralTesting\Tests\Fake;

use AstralTesting\Fake\EventFake;
use AstralTesting\Fake\LogFake;
use AstralTesting\Fake\MailFake;
use AstralTesting\Tests\Fixtures\DummyEvent;
use Core\Container;
use Core\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;

final class FakeAssertionsTest extends TestCase
{
    public function testEventFakeAssertDispatched(): void
    {
        $fake = new EventFake();
        $fake->dispatch(new DummyEvent('ada'));

        $fake->assertDispatched(DummyEvent::class);
        $fake->assertDispatched(DummyEvent::class, static fn (DummyEvent $e): bool => $e->name === 'ada');
        $fake->assertDispatchedTimes(DummyEvent::class, 1);
    }

    public function testEventFakeListenOnDispatcher(): void
    {
        $fake = new EventFake();
        $dispatcher = new EventDispatcher(new Container());
        $fake->listenOn($dispatcher, DummyEvent::class);

        $dispatcher->dispatch(new DummyEvent('bob'));

        $fake->assertDispatched(DummyEvent::class, static fn (DummyEvent $e): bool => $e->name === 'bob');
    }

    public function testEventFakeAssertNotDispatched(): void
    {
        $fake = new EventFake();
        $fake->assertNotDispatched(DummyEvent::class);
    }

    public function testMailFakeAssertSent(): void
    {
        $mail = new MailFake();
        $mail->send('ada@example.test', 'Welcome', '<p>Hello Ada</p>');

        $mail->assertSent();
        $mail->assertSentTo('ada@example.test');
        $mail->assertSent(static fn (array $m): bool => str_contains($m['html'], 'Ada'));
        $mail->assertSentCount(1);
    }

    public function testMailFakeNothingSent(): void
    {
        $mail = new MailFake();
        $mail->assertNothingSent();
    }

    public function testLogFakeAssertLogged(): void
    {
        $log = new LogFake();
        $log->info('Order paid', ['id' => 42]);
        $log->error('Payment failed');

        $log->assertLogged('info', 'Order paid');
        $log->assertLogged('info', static fn (array $e): bool => ($e['context']['id'] ?? null) === 42);
        $log->assertLogged('error', 'Payment failed');
        $log->assertNotLogged('info', 'does-not-exist');
    }
}
