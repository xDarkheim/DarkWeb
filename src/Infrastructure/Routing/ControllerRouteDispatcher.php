<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Infrastructure\Bootstrap\BootstrapContext;

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
            $result = BootstrapContext::configProvider()?->moduleConfig($moduleConfig);
            BootstrapContext::runtimeState()?->setModuleConfig(is_array($result) ? $result : []);
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, 'render')) {
            throw new \RuntimeException('Invalid controller for route: ' . $page);
        }

        $controller->render();
        return true;
    }
}

