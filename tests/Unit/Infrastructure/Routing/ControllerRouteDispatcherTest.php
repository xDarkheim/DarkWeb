<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Dispatchers\ControllerRouteDispatcher;
use Darkheim\Infrastructure\Routing\Registries\WebRouteRegistry;
use PHPUnit\Framework\TestCase;

final class ControllerRouteDispatcherTest extends TestCase
{
    public function testDispatchReturnsFalseForUnknownRoute(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, "<?php return [];\n");

        $dispatcher = new ControllerRouteDispatcher(new WebRouteRegistry($routesFile));

        $this->assertFalse($dispatcher->dispatch('unknown'));

        @unlink($routesFile);
    }

    public function testDispatchCallsControllerRender(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_routes_' . uniqid('', true) . '.php';
        file_put_contents(
            $routesFile,
            <<<'PHP'
                <?php
                return [
                    'login' => [
                        'controller' => 'Tests\\Unit\\Infrastructure\\Routing\\FixtureRouteController',
                        'module_config' => 'login',
                    ],
                ];
                PHP
        );

        $GLOBALS['__route_dispatch_called'] = false;

        $dispatcher = new ControllerRouteDispatcher(new WebRouteRegistry($routesFile));

        $this->assertTrue($dispatcher->dispatch('login'));
        $this->assertTrue((bool) $GLOBALS['__route_dispatch_called']);

        @unlink($routesFile);
    }
}

final class FixtureRouteController
{
    public function render(): void
    {
        $GLOBALS['__route_dispatch_called'] = true;
    }
}
