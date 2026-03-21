<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Application\Admincp\CacheManagerController;
use Darkheim\Application\Admincp\HomeController;
use Darkheim\Application\Admincp\NewRegistrationsController;
use Darkheim\Application\Admincp\OnlineAccountsController;
use Darkheim\Infrastructure\Routing\AdmincpRouteRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdmincpRouteRegistryTest extends TestCase
{
    public function testRouteForReturnsConfiguredAdmincpEntry(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_admincp_registry_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, <<<'PHP'
<?php
return [
    'home' => [
        'controller' => 'Tests\\Admincp\\HomeController',
    ],
];
PHP
);

        $registry = new AdmincpRouteRegistry($routesFile);

        $route = $registry->routeFor('home');

        $this->assertIsArray($route);
        $this->assertSame('Tests\\Admincp\\HomeController', $route['controller'] ?? null);
        $this->assertNull($registry->routeFor('missing'));

        @unlink($routesFile);
    }

    #[DataProvider('configuredAdmincpRoutesProvider')]
    public function testConfiguredAdmincpRoutesUseControllers(string $module, string $controller): void
    {
        $registry = new AdmincpRouteRegistry();

        $route = $registry->routeFor($module);

        $this->assertIsArray($route);
        $this->assertSame($controller, $route['controller'] ?? null);
    }

    /** @return array<int, array{0:string,1:string}> */
    public static function configuredAdmincpRoutesProvider(): array
    {
        return [
            ['cachemanager', CacheManagerController::class],
            ['home', HomeController::class],
            ['newregistrations', NewRegistrationsController::class],
            ['onlineaccounts', OnlineAccountsController::class],
        ];
    }
}

