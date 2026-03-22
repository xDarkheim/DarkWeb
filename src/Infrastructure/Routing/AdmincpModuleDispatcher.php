<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Infrastructure\Bootstrap\BootstrapContext;

final class AdmincpModuleDispatcher
{
    private AdmincpRouteRegistry $registry;

    public function __construct(?AdmincpRouteRegistry $registry = null)
    {
        $this->registry = $registry ?? new AdmincpRouteRegistry();
    }

    /**
     * @param array<string, mixed> $context Reserved for future dispatcher context.
     */
    public function dispatch(string $module, array $context = []): bool
    {
        $route = $this->registry->routeFor($module);
        $moduleConfig = is_array($route) ? ($route['module_config'] ?? null) : null;
        if (is_string($moduleConfig) && $moduleConfig !== '') {
            BootstrapContext::loadModuleConfig($moduleConfig);
        }

        $controllerClass = is_array($route) ? ($route['controller'] ?? null) : null;

        if (is_string($controllerClass) && $controllerClass !== '') {
            if (!class_exists($controllerClass)) {
                return false;
            }

            $controller = new $controllerClass();
            if (!method_exists($controller, 'render')) {
                return false;
            }

            $controller->render();
            return true;
        }

        return false;
    }
}

