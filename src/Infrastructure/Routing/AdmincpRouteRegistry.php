<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class AdmincpRouteRegistry
{
    private string $routesFile;

    /** @var array<string, array<string, string|null>>|null */
    private ?array $routes = null;

    public function __construct(?string $routesFile = null)
    {
        $projectRoot = dirname(__DIR__, 3);
        $this->routesFile = $routesFile ?? $projectRoot . '/config/routes.admincp.php';
    }

    /**
     * @return array{module_config?: string|null, controller?: string|null}|null
     */
    public function routeFor(string $module): ?array
    {
        $routes = $this->load();
        $entry = $routes[$module] ?? null;

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

