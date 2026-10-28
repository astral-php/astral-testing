# astral-php/astral-testing

Helpers de test pour applications [Astral](https://github.com/astral-php) — HTTP smokes, factories, fakes (events / mails / logs).

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Version](https://img.shields.io/badge/version-0.1.0-blue)](./CHANGELOG.md)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> **Version 0.1.0** — HttpTestCase, factories, fakes (events / mails / logs).  
> Roadmap / checklist : `astral-informations/astral-testing/`

## Prérequis

- PHP ^8.1  
- `astral-php/astral-core` ^1.2  
- PHPUnit ^10.5 \|\| ^11 (dev)

## Installation

```bash
composer require astral-php/astral-testing --dev
```

Développement local (path) :

```json
{
  "repositories": [{
    "type": "path",
    "url": "../components-astral/astral-testing",
    "options": { "symlink": true }
  }],
  "require-dev": {
    "astral-php/astral-testing": "@dev"
  }
}
```

## HttpTestCase

Étendre `AstralTesting\Http\HttpTestCase` et définir la racine de l’app :

```php
use AstralTesting\Http\HttpTestCase;

final class SmokeRoutesTest extends HttpTestCase
{
    protected function applicationPath(): string
    {
        return dirname(__DIR__, 2);
    }

    public function testHome(): void
    {
        $this->assertStatus($this->get('/'), 200);
    }

    public function testLoginPost(): void
    {
        $r = $this->post('/login', $this->withCsrf([
            'email'    => 'admin@example.test',
            'password' => 'secret',
        ]));
        $this->assertStatus($r, 302);
    }
}
```

| Helper | Rôle |
|--------|------|
| `get` / `post` / `put` / `patch` / `delete` | Requête → `Response` (sans `send()`) |
| `withCsrf($data)` | Ajoute `_token` |
| `withSession($values)` | Préremplit `$_SESSION` |
| `assertStatus` / `assertSee` | Assertions Response |

Exemple copiable : [`examples/HttpSmokeTest.php`](./examples/HttpSmokeTest.php).

## Factories

```php
use AstralTesting\Factory\Factory;

final class UserFactory extends Factory
{
    protected function definition(): array
    {
        $n = $this->sequence();

        return [
            'email' => "user{$n}@example.test",
            'role'  => 'user',
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }
}

$attrs = UserFactory::new()->make();
$user  = UserFactory::new()
    ->creating(fn (array $a) => $userDao->insert($a))
    ->create(['email' => 'ada@example.test']);
```

`make()` = attributs seuls · `create()` = persist via `creating(callable)` ou `persist()` overridé.

Exemple : [`examples/UserFactory.php`](./examples/UserFactory.php).

## Fakes

`Mailer` et `Logger` du core sont **`final`** : injecter les fakes en test (container / constructeur).

```php
use AstralTesting\Fake\EventFake;
use AstralTesting\Fake\MailFake;
use AstralTesting\Fake\LogFake;

$events = new EventFake();
$events->listenOn($dispatcher, UserRegistered::class);
// … action sous test …
$events->assertDispatched(UserRegistered::class);

$mail = new MailFake(); // binder à la place de Mailer
$mail->assertSentTo('ada@example.test');

$log = new LogFake();
$log->assertLogged('info', 'Order paid');
```

## PHPUnit app (stub)

Copier [`stubs/phpunit.xml.example`](./stubs/phpunit.xml.example) vers la racine de ton app (ajuster `bootstrap` / dossiers).

## Développement du package

```bash
cd components-astral/astral-testing
composer install
composer test
```

## API (namespaces)

| Namespace | Contenu |
|-----------|---------|
| `AstralTesting\Http\` | `HttpTestCase` |
| `AstralTesting\Factory\` | `Factory` |
| `AstralTesting\Fake\` | `EventFake`, `MailFake`, `LogFake` |
| `AstralTesting\Package` | Métadonnées |

## Licence

MIT
