<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\SubpageRouteRegistry;
use PHPUnit\Framework\TestCase;

final class SubpageRouteRegistryTest extends TestCase
{
    public function testRouteForReturnsConfiguredEntry(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_sub_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, <<<'PHP'
<?php
return [
    'usercp/addstats' => [
        'module_config' => 'usercp.addstats',
    ],
];
PHP
);

        $registry = new SubpageRouteRegistry($routesFile);
        $route = $registry->routeFor('usercp', 'addstats');

        $this->assertIsArray($route);
        $this->assertSame('usercp.addstats', $route['module_config']);
        $this->assertNull($registry->routeFor('usercp', 'missing'));

        @unlink($routesFile);
    }
}

