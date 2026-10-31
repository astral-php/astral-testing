# Changelog — astral-php/astral-testing
## [0.2.0] — 2026-10-31

### Modifié

- PHP ^8.3 + astral-core ^2.1 (feuille 2.1 P6)


## [0.1.0] — 2026-10-28

### Ajouté
- Socle Composer : namespace `AstralTesting\`, PHPUnit, dépendance `astral-core` ^1.2.
- Classe `AstralTesting\Package` (métadonnées).
- `AstralTesting\Http\HttpTestCase` : `get` / `post` / spoof PUT·PATCH·DELETE, `withCsrf`, `csrfToken`, `withSession`, `session`, `assertStatus`, `assertSee`.
- `AstralTesting\Factory\Factory` : `make` / `create` / `state` / `count` / `creating` + exemple `UserFactory`.
- Fakes `EventFake` / `MailFake` / `LogFake` + assertions.
- Doc README, `examples/`, stub `stubs/phpunit.xml.example`.
