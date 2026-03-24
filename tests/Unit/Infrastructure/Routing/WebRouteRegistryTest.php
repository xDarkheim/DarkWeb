<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Registries\WebRouteRegistry;
use PHPUnit\Framework\TestCase;

final class WebRouteRegistryTest extends TestCase
{
    public function testControllerAndModuleConfigAreResolvedFromRoutesFile(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_routes_' . uniqid('', true) . '.php';
        file_put_contents(
            $routesFile,
            <<<'PHP'
                <?php
                return [
                    'login' => [
                        'controller' => 'Tests\\Fixtures\\LoginController',
                        'module_config' => 'login',
                    ],
                ];
                PHP
        );

        $registry = new WebRouteRegistry($routesFile);

        $this->assertSame('Tests\\Fixtures\\LoginController', $registry->controllerForPage('login'));
        $this->assertSame('login', $registry->moduleConfigForPage('login'));
        $this->assertNull($registry->controllerForPage('unknown'));

        @unlink($routesFile);
    }
}
