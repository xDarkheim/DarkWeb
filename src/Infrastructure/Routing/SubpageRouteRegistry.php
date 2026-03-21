<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class SubpageRouteRegistry
{
    private string $routesFile;

    /** @var array<string, array<string, string|null>>|null */
    private ?array $routes = null;

    public function __construct(?string $routesFile = null)
    {
        $projectRoot = dirname(__DIR__, 3);
        $this->routesFile = $routesFile ?? $projectRoot . '/config/routes.subpages.php';
    }

    /**
     * @return array{module_config?: string|null, controller?: string|null}|null
     */
    public function routeFor(string $page, string $subpage): ?array
    {
        $key = $page . '/' . $subpage;
        $routes = $this->load();
        $entry = $routes[$key] ?? null;

        return is_array($entry) ? $entry : null;
    }

    /**
     * @return array<string, array<string, string|null>>
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

