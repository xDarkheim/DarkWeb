<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\ControllerRouteDispatcher;
use Darkheim\Infrastructure\Routing\Handler;
use Darkheim\Infrastructure\Routing\WebRouteRegistry;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\ArrayQueryStore;
use Tests\Stubs\ArraySessionStore;

final class HandlerMigrationGateTest extends TestCase
{
    public function testTopLevelPageDoesNotFallBackToLegacyModuleInclude(): void
    {
        $page = '__migrated_gate_test';

        $modulePath = __PATH_MODULES__ . $page . '.php';
        file_put_contents($modulePath, "<?php \$GLOBALS['__migrated_legacy_fallback_called'] = true;\n");

        $routesPath = sys_get_temp_dir() . '/routes_' . uniqid('', true) . '.php';
        file_put_contents($routesPath, "<?php return [];\n");

        $GLOBALS['__migrated_legacy_fallback_called'] = false;

        $handler = new Handler(
            new ArraySessionStore(),
            new ArrayQueryStore(),
            new ControllerRouteDispatcher(new WebRouteRegistry($routesPath)),
            null,
            null,
            null,
            null,
            null,
            null
        );

        // Should hit 404 path before touching legacy include.
        $handler->loadModule($page, null);

        $this->assertFalse((bool) $GLOBALS['__migrated_legacy_fallback_called']);

        @unlink($modulePath);
        @unlink($routesPath);
    }
}

