<?php

declare(strict_types=1);

namespace AstralTesting\Fake;

use Core\Events\EventDispatcher;
use Core\Events\EventInterface;
use PHPUnit\Framework\Assert;

/**
 * Enregistre les événements dispatchés (tests).
 *
 * Brancher via {@see listenOn()} sur un EventDispatcher réel,
 * ou appeler {@see record()} / {@see dispatch()} depuis le code de test.
 */
final class EventFake
{
    /** @var list<EventInterface> */
    private array $dispatched = [];

    public function record(EventInterface $event): void
    {
        $this->dispatched[] = $event;
    }

    /** Alias de record() — API proche du dispatcher. */
    public function dispatch(EventInterface $event): void
    {
        $this->record($event);
    }

    /**
     * Enregistre un listener qui capture chaque classe d’événement donnée.
     *
     * @param class-string<EventInterface> ...$eventClasses
     */
    public function listenOn(EventDispatcher $dispatcher, string ...$eventClasses): void
    {
        foreach ($eventClasses as $eventClass) {
            $dispatcher->listen($eventClass, function (EventInterface $event): void {
                $this->record($event);
            });
        }
    }

    public function reset(): void
    {
        $this->dispatched = [];
    }

    /**
     * @return list<EventInterface>
     */
    public function dispatched(?string $eventClass = null): array
    {
        if ($eventClass === null) {
            return $this->dispatched;
        }

        return array_values(array_filter(
            $this->dispatched,
            static fn (EventInterface $e): bool => $e instanceof $eventClass,
        ));
    }

    /**
     * @param class-string<EventInterface> $eventClass
     * @param (callable(EventInterface): bool)|null $callback
     */
    public function assertDispatched(string $eventClass, ?callable $callback = null): void
    {
        $matches = $this->dispatched($eventClass);

        Assert::assertNotEmpty(
            $matches,
            "Failed asserting that event [{$eventClass}] was dispatched.",
        );

        if ($callback === null) {
            return;
        }

        foreach ($matches as $event) {
            if ($callback($event)) {
                Assert::assertTrue(true);

                return;
            }
        }

        Assert::fail("Event [{$eventClass}] was dispatched but failed the callback assertion.");
    }

    /**
     * @param class-string<EventInterface> $eventClass
     */
    public function assertDispatchedTimes(string $eventClass, int $times): void
    {
        $count = count($this->dispatched($eventClass));
        Assert::assertSame(
            $times,
            $count,
            "Failed asserting that [{$eventClass}] was dispatched {$times} time(s). Dispatched {$count} time(s).",
        );
    }

    /**
     * @param class-string<EventInterface> $eventClass
     */
    public function assertNotDispatched(string $eventClass): void
    {
        Assert::assertEmpty(
            $this->dispatched($eventClass),
            "Failed asserting that event [{$eventClass}] was not dispatched.",
        );
    }
}
