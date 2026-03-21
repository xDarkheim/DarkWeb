<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\ApiRouteDispatcher;
use Darkheim\Infrastructure\Routing\ApiRouteRegistry;
use PHPUnit\Framework\TestCase;

final class ApiTestController
{
    public function render(): void
    {
        $GLOBALS['__api_controller_dispatched'] = true;
    }
}

final class ApiRouteDispatcherTest extends TestCase
{
    public function testDispatchReturnsFalseForMissingEndpoint(): void
    {
        $dispatcher = new ApiRouteDispatcher();
        $this->assertFalse($dispatcher->dispatch('missing_endpoint'));
    }

    public function testDispatchReturnsFalseWhenControllerClassDoesNotExist(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_api_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, <<<'PHP'
<?php
return [
    'events' => [
        'controller' => 'Tests\\Missing\\Controller',
    ],
];
PHP
);

        $dispatcher = new ApiRouteDispatcher(new ApiRouteRegistry($routesFile));
        $this->assertFalse($dispatcher->dispatch('events'));

        @unlink($routesFile);
    }

    public function testDispatchUsesControllerBackedApiRoute(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_api_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, sprintf(
            <<<'PHP'
<?php
return [
    'events' => [
        'controller' => %s::class,
    ],
];
PHP,
            '\\' . ApiTestController::class
        ));

        $GLOBALS['__api_controller_dispatched'] = false;

        $dispatcher = new ApiRouteDispatcher(new ApiRouteRegistry($routesFile));

        $this->assertTrue($dispatcher->dispatch('events'));
        $this->assertTrue((bool) $GLOBALS['__api_controller_dispatched']);

        @unlink($routesFile);
    }
}

