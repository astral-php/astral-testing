<?php

declare(strict_types=1);

namespace AstralTesting\Tests\Fixtures;

use AstralTesting\Factory\Factory;

/**
 * Factory de démo pour les tests du package (pas de modèle app).
 */
final class UserFactory extends Factory
{
    protected function definition(): array
    {
        $n = $this->sequence();

        return [
            'name'  => 'User ' . $n,
            'email' => 'user' . $n . '@example.test',
            'role'  => 'user',
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }
}
