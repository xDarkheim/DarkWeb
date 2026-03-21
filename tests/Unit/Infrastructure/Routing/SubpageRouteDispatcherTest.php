<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\SubpageRouteDispatcher;
use Darkheim\Infrastructure\Routing\SubpageRouteRegistry;
use PHPUnit\Framework\TestCase;

final class SubpageRouteDispatcherTest extends TestCase
{
    public function testDispatchIncludesMappedSubpageFile(): void
    {
        $page = '__subpage_test';
        $subpage = 'demo';

        $moduleDir = __PATH_MODULES__ . $page;
        @mkdir($moduleDir, 0777, true);
        $moduleFile = $moduleDir . '/' . $subpage . '.php';
        file_put_contents($moduleFile, "<?php \$GLOBALS['__subpage_route_dispatched'] = true;\n");

        $routesFile = sys_get_temp_dir() . '/darkcore_sub_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, "<?php return ['{$page}/{$subpage}' => ['module_config' => null]];\n");

        $GLOBALS['__subpage_route_dispatched'] = false;

        $dispatcher = new SubpageRouteDispatcher(new SubpageRouteRegistry($routesFile));
        $this->assertTrue($dispatcher->dispatch($page, $subpage));
        $this->assertTrue((bool) $GLOBALS['__subpage_route_dispatched']);

        @unlink($routesFile);
        @unlink($moduleFile);
        @rmdir($moduleDir);
    }

    public function testDispatchReturnsFalseWhenRouteIsMissing(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_sub_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, "<?php return [];\n");

        $dispatcher = new SubpageRouteDispatcher(new SubpageRouteRegistry($routesFile));
        $this->assertFalse($dispatcher->dispatch('usercp', 'missing'));

        @unlink($routesFile);
    }
}

