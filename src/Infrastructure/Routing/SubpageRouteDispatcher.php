<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class SubpageRouteDispatcher
{
    private SubpageRouteRegistry $registry;
    private ?string $subpageViewsPath;

    public function __construct(?SubpageRouteRegistry $registry = null, ?string $subpageViewsPath = null)
    {
        $this->registry = $registry ?? new SubpageRouteRegistry();
        $this->subpageViewsPath = $subpageViewsPath;
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

        $controllerClass = $route['controller'] ?? null;
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


        $basePath = $this->subpageViewsPath;
        if ($basePath === null) {
            if (!defined('__PATH_VIEWS__')) {
                return false;
            }
            $basePath = (string) constant('__PATH_VIEWS__') . 'subpages/';
        }

        $subpageFile = $basePath . $page . '/' . $subpage . '.php';
        if (!is_file($subpageFile)) {
            return false;
        }

        include $subpageFile;
        return true;
    }
}
