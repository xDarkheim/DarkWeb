<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Dispatchers\AdmincpModuleDispatcher;
use Darkheim\Infrastructure\Routing\Registries\AdmincpRouteRegistry;
use PHPUnit\Framework\TestCase;

final class AdmincpTestController
{
    public function render(): void
    {
        $GLOBALS['__admin_controller_dispatched'] = true;
    }
}

final class AdmincpModuleDispatcherTest extends TestCase
{
    public function testDispatchReturnsFalseForMissingModule(): void
    {
        $dispatcher = new AdmincpModuleDispatcher();
        $this->assertFalse($dispatcher->dispatch('missing_admin_module'));
    }

    public function testDispatchReturnsFalseWhenRouteHasNoController(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_admincp_routes_' . uniqid('', true) . '.php';
        file_put_contents(
            $routesFile,
            <<<'PHP'
                <?php
                return [
                    'home' => [
                        'module_config' => 'home',
                    ],
                ];
                PHP
        );

        $dispatcher = new AdmincpModuleDispatcher(new AdmincpRouteRegistry($routesFile));

        $this->assertFalse($dispatcher->dispatch('home'));

        @unlink($routesFile);
    }

    public function testDispatchUsesControllerBackedAdmincpRoute(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_admincp_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, sprintf(
            <<<'PHP'
                <?php
                return [
                    'home' => [
                        'module_config' => 'home',
                        'controller' => %s::class,
                    ],
                ];
                PHP,
            '\\' . AdmincpTestController::class,
        ));

        $GLOBALS['__admin_controller_dispatched'] = false;

        $dispatcher = new AdmincpModuleDispatcher(new AdmincpRouteRegistry($routesFile));

        $this->assertTrue($dispatcher->dispatch('home'));
        $this->assertTrue((bool) $GLOBALS['__admin_controller_dispatched']);

        @unlink($routesFile);
    }
}
