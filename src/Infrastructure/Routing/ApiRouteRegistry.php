<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class ApiRouteRegistry
{
    private string $routesFile;

    /** @var array<string, array<string, string>>|null */
    private ?array $routes = null;

    public function __construct(?string $routesFile = null)
    {
        $projectRoot = dirname(__DIR__, 3);
        $this->routesFile = $routesFile ?? $projectRoot . '/config/routes.api.php';
    }

    /**
     * @return array{controller?: string}|null
     */
    public function routeFor(string $endpoint): ?array
    {
        $routes = $this->load();
        $entry = $routes[$endpoint] ?? null;

        return is_array($entry) ? $entry : null;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function load(): array
    {
        if (is_array($this->routes)) {
            return $this->routes;
        }

        if (!is_file($this->routesFile)) {
            $this->routes = [];
            return $this->routes;
        }

        /** @var mixed $data */
        $data = include $this->routesFile;
        $this->routes = is_array($data) ? $data : [];

        return $this->routes;
    }
}

