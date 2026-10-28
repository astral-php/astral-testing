<?php

declare(strict_types=1);

namespace AstralTesting\Http;

use Core\Auth\Auth;
use Core\Container;
use Core\CsrfGuard;
use Core\Http\Response;
use Core\Request;
use Core\Router;
use Core\Session;
use Core\View;
use Database\Connection;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Base PHPUnit pour smokes / tests HTTP Astral (Router::handle → Response, sans send()).
 *
 * L’app hôte étend cette classe et implémente {@see applicationPath()}.
 * Requiert astral-core ^1.2 (Router::handle).
 */
abstract class HttpTestCase extends TestCase
{
    protected string $basePath = '';

    private ?Session $session = null;

    private ?CsrfGuard $csrf = null;

    /**
     * Racine de l’application Astral (dossier contenant `config/`).
     */
    abstract protected function applicationPath(): string;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = rtrim(str_replace('\\', '/', $this->applicationPath()), '/');
        if ($this->basePath === '' || !is_dir($this->basePath . '/config')) {
            throw new RuntimeException('applicationPath() doit pointer vers une app Astral avec config/.');
        }

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', $this->basePath);
        } elseif (rtrim(str_replace('\\', '/', (string) BASE_PATH), '/') !== $this->basePath) {
            throw new RuntimeException(
                'BASE_PATH déjà défini à une autre racine. Un seul applicationPath par processus de test.',
            );
        }

        foreach (['storage/logs', 'storage/cache', 'database'] as $dir) {
            $path = $this->basePath . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        Connection::reset();
        $_SESSION = [];
        $this->session = null;
        $this->csrf = null;
    }

    protected function tearDown(): void
    {
        Connection::reset();
        $this->session = null;
        $this->csrf = null;
        parent::tearDown();
    }

    protected function get(string $uri): Response
    {
        return $this->request('GET', $uri);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function post(string $uri, array $data = []): Response
    {
        return $this->request('POST', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function put(string $uri, array $data = []): Response
    {
        $data['_method'] = 'PUT';

        return $this->request('POST', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function patch(string $uri, array $data = []): Response
    {
        $data['_method'] = 'PATCH';

        return $this->request('POST', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function delete(string $uri, array $data = []): Response
    {
        $data['_method'] = 'DELETE';

        return $this->request('POST', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data Corps POST (ignoré pour GET)
     */
    protected function request(string $method, string $uri, array $data = []): Response
    {
        Connection::reset();

        $method = strtoupper($method);
        $_SERVER['REQUEST_METHOD'] = $method === 'GET' ? 'GET' : 'POST';
        $_SERVER['REQUEST_URI'] = $uri;
        unset($_SERVER['CONTENT_TYPE']);

        [$path, $query] = array_pad(explode('?', $uri, 2), 2, '');
        $_GET = [];
        if ($query !== '') {
            parse_str($query, $parsed);
            /** @var array<string, mixed> $parsed */
            $_GET = $parsed;
        }

        $_POST = $method === 'GET' ? [] : $data;

        if (is_file($this->basePath . '/.env')) {
            Dotenv::createImmutable($this->basePath)->safeLoad();
        }

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['DB_DRIVER'] = 'sqlite';

        /** @var array<string, mixed> $appConfig */
        $appConfig = require $this->basePath . '/config/app.php';
        /** @var array<string, mixed> $dbConfig */
        $dbConfig = require $this->basePath . '/config/database.php';
        $dbConfig['driver'] = 'sqlite';
        $dbConfig['database'] = ':memory:';

        date_default_timezone_set((string) ($appConfig['timezone'] ?? 'UTC'));

        $container = new Container();

        /** @var list<class-string> $providers */
        $providers = require $this->basePath . '/config/dependencies.php';
        foreach ($providers as $providerClass) {
            (new $providerClass())->register($container, $appConfig, $dbConfig);
        }

        /** @var Session $session */
        $session = $container->make(Session::class);
        $session->start();
        $this->session = $session;

        /** @var CsrfGuard $csrf */
        $csrf = $container->make(CsrfGuard::class);
        $this->csrf = $csrf;

        /** @var View $view */
        $view = $container->make(View::class);
        $view->share('session', $session);
        $view->share('csrf', $csrf);
        $view->share('auth', $container->make(Auth::class));
        $view->share('viewEngine', $view);

        $request = $container->make(Request::class);
        $router = new Router(request: $request, container: $container);

        $register = require $this->basePath . '/config/routes.php';
        $register($router);

        $response = $router->handle();

        $this->assertInstanceOf(Response::class, $response);

        return $response;
    }

    /**
     * Ajoute le token CSRF de session courante (crée la session si besoin).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function withCsrf(array $data = []): array
    {
        $data['_token'] = $this->csrfToken();

        return $data;
    }

    protected function csrfToken(): string
    {
        $this->ensureSessionBag();

        if ($this->csrf === null) {
            $session = new Session();
            $session->start();
            $this->session = $session;
            $this->csrf = new CsrfGuard($session);
        }

        return $this->csrf->token();
    }

    /**
     * @param array<string, mixed> $values
     */
    protected function withSession(array $values): static
    {
        $this->ensureSessionBag();
        foreach ($values as $key => $value) {
            $_SESSION[$key] = $value;
        }

        return $this;
    }

    protected function session(): Session
    {
        $this->ensureSessionBag();

        if ($this->session === null) {
            $this->session = new Session();
            $this->session->start();
        }

        return $this->session;
    }

    protected function assertStatus(Response $response, int $status): void
    {
        $this->assertSame($status, $response->getStatus());
    }

    protected function assertSee(Response $response, string $needle): void
    {
        $this->assertStringContainsString($needle, $response->getContent());
    }

    private function ensureSessionBag(): void
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
    }
}
