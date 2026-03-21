<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class SubpageRouteDispatcher
{
    private SubpageRouteRegistry $registry;

    public function __construct(?SubpageRouteRegistry $registry = null)
    {
        $this->registry = $registry ?? new SubpageRouteRegistry();
    }

    public function dispatch(string $page, string $subpage): bool
    {
        $route = $this->registry->routeFor($page, $subpage);
        if (!is_array($route)) {
            return false;
        }

        $moduleConfig = $route['module_config'] ?? null;
        if (is_string($moduleConfig) && $moduleConfig !== '') {
            @loadModuleConfigs($moduleConfig);
        }

        $moduleFile = __PATH_MODULES__ . $page . '/' . $subpage . '.php';
        if (!is_file($moduleFile)) {
            return false;
        }

        include $moduleFile;
        return true;
    }
}

