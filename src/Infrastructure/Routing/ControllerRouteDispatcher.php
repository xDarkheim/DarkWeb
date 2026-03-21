<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class ControllerRouteDispatcher
{
    private WebRouteRegistry $registry;

    public function __construct(?WebRouteRegistry $registry = null)
    {
        $this->registry = $registry ?? new WebRouteRegistry();
    }

    public function dispatch(string $page): bool
    {
        $controllerClass = $this->registry->controllerForPage($page);
        if (!is_string($controllerClass) || $controllerClass === '') {
            return false;
        }

        $moduleConfig = $this->registry->moduleConfigForPage($page);
        if (is_string($moduleConfig) && $moduleConfig !== '') {
            // Preserve legacy behavior so modules using mconfig() still work.
            @loadModuleConfigs($moduleConfig);
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, 'render')) {
            throw new \RuntimeException('Invalid controller for route: ' . $page);
        }

        $controller->render();
        return true;
    }
}

