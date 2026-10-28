<?php

declare(strict_types=1);

namespace App\Tests\Factories;

use AstralTesting\Factory\Factory;

/**
 * Exemple d’usine User pour une app Astral (à copier sous tests/Factories/).
 *
 * create() : brancher le DAO, ex. :
 *   UserFactory::new()->creating(fn (array $a) => $userDao->create($a))->create();
 */
final class UserFactory extends Factory
{
    protected function definition(): array
    {
        $n = $this->sequence();

        return [
            'name'     => 'User ' . $n,
            'email'    => 'user' . $n . '@example.test',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role'     => 'user',
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }
}
