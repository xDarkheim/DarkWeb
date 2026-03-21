<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class ApiRouteDispatcher
{
    private ApiRouteRegistry $registry;

    public function __construct(?ApiRouteRegistry $registry = null)
    {
        $this->registry = $registry ?? new ApiRouteRegistry();
    }

    public function dispatch(string $endpoint): bool
    {
        $route = $this->registry->routeFor($endpoint);
        if (!is_array($route)) {
            return false;
        }

        $controllerClass = $route['controller'] ?? null;
        if (!is_string($controllerClass) || $controllerClass === '' || !class_exists($controllerClass)) {
            return false;
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, 'render')) {
            return false;
        }

        $controller->render();
        return true;
    }
}

