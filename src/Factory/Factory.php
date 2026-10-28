<?php

declare(strict_types=1);

namespace AstralTesting\Factory;

use LogicException;

/**
 * Factory de test — `make` (attributs) / `create` (persist via callback ou override).
 *
 * @phpstan-consistent-constructor
 */
abstract class Factory
{
    private int $count = 1;

    /** @var list<array<string, mixed>|callable(array<string, mixed>): array<string, mixed>> */
    private array $states = [];

    /** @var (callable(array<string, mixed>): mixed)|null */
    private $persister = null;

    private int $sequence = 0;

    public static function new(): static
    {
        return new static();
    }

    /**
     * Attributs par défaut (sans persistance).
     *
     * @return array<string, mixed>
     */
    abstract protected function definition(): array;

    /**
     * @param array<string, mixed>|callable(array<string, mixed>): array<string, mixed> $state
     */
    public function state(array|callable $state): static
    {
        $clone = clone $this;
        $clone->states[] = $state;

        return $clone;
    }

    public function count(int $count): static
    {
        if ($count < 1) {
            throw new LogicException('count() doit être >= 1.');
        }

        $clone = clone $this;
        $clone->count = $count;

        return $clone;
    }

    /**
     * Persiste via callback (DAO, repository…). Chaînable.
     *
     * @param callable(array<string, mixed>): mixed $persister
     */
    public function creating(callable $persister): static
    {
        $clone = clone $this;
        $clone->persister = $persister;

        return $clone;
    }

    /**
     * Construit les attributs sans écrire en base.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function make(array $overrides = []): array
    {
        if ($this->count === 1) {
            return $this->makeOne($overrides);
        }

        $items = [];
        for ($i = 0; $i < $this->count; $i++) {
            $items[] = $this->makeOne($overrides);
        }

        return $items;
    }

    /**
     * Persiste un ou plusieurs enregistrements.
     *
     * @param array<string, mixed> $overrides
     * @return mixed|list<mixed>
     */
    public function create(array $overrides = []): mixed
    {
        if ($this->count === 1) {
            return $this->createOne($overrides);
        }

        $items = [];
        for ($i = 0; $i < $this->count; $i++) {
            $items[] = $this->createOne($overrides);
        }

        return $items;
    }

    /**
     * Index de séquence (1-based) pour emails uniques, etc.
     */
    protected function sequence(): int
    {
        return $this->sequence;
    }

    /**
     * Hook de persistance — override dans l’app, ou utilise {@see creating()}.
     *
     * @param array<string, mixed> $attributes
     */
    protected function persist(array $attributes): mixed
    {
        if ($this->persister !== null) {
            return ($this->persister)($attributes);
        }

        throw new LogicException(
            static::class . '::create() nécessite creating(callable) ou un override de persist().',
        );
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function makeOne(array $overrides): array
    {
        $this->sequence++;

        $attributes = array_merge($this->definition(), $overrides);

        foreach ($this->states as $state) {
            if (is_callable($state)) {
                /** @var array<string, mixed> $attributes */
                $attributes = array_merge($attributes, $state($attributes));
            } else {
                $attributes = array_merge($attributes, $state);
            }
        }

        // Les overrides gagnent toujours sur les states
        return array_merge($attributes, $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createOne(array $overrides): mixed
    {
        return $this->persist($this->makeOne($overrides));
    }
}
