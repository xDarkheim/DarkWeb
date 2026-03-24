<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Dispatchers\SubpageRouteDispatcher;
use Darkheim\Infrastructure\Routing\Registries\SubpageRouteRegistry;
use PHPUnit\Framework\TestCase;

final class SubpageRouteDispatcherTest extends TestCase
{
    public function testDispatchExecutesControllerWhenRouteDefinesController(): void
    {
        if (! class_exists('Tests\\Stubs\\SubpageControllerStub', false)) {
            eval('namespace Tests\\Stubs; final class SubpageControllerStub { public function render(): void { $GLOBALS["__subpage_controller_dispatched"] = true; } }');
        }

        $routesFile = sys_get_temp_dir() . '/darkcore_sub_routes_' . uniqid('', true) . '.php';
        file_put_contents(
            $routesFile,
            "<?php return ['x/y' => ['module_config' => null, 'controller' => 'Tests\\\\Stubs\\\\SubpageControllerStub']];\n",
        );

        $GLOBALS['__subpage_controller_dispatched'] = false;

        $dispatcher = new SubpageRouteDispatcher(new SubpageRouteRegistry($routesFile));
        $this->assertTrue($dispatcher->dispatch('x', 'y'));
        $this->assertTrue((bool) $GLOBALS['__subpage_controller_dispatched']);

        @unlink($routesFile);
    }

    public function testDispatchIncludesMappedSubpageFile(): void
    {
        $page    = '__subpage_test';
        $subpage = 'demo';

        $subpageViewsBase = sys_get_temp_dir() . '/darkcore_subpages_' . uniqid('', true) . '/';
        $subpageDir       = $subpageViewsBase . $page;
        @mkdir($subpageDir, 0o777, true);
        $subpageFile = $subpageDir . '/' . $subpage . '.php';
        file_put_contents($subpageFile, "<?php \$GLOBALS['__subpage_route_dispatched'] = true;\n");

        $routesFile = sys_get_temp_dir() . '/darkcore_sub_routes_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, "<?php return ['{$page}/{$subpage}' => ['module_config' => null]];\n");

        $GLOBALS['__subpage_route_dispatched'] = false;

        $dispatcher = new SubpageRouteDispatcher(new SubpageRouteRegistry($routesFile), $subpageViewsBase);
        $this->assertTrue($dispatcher->dispatch($page, $subpage));
        $this->assertTrue((bool) $GLOBALS['__subpage_route_dispatched']);

        @unlink($routesFile);
        @unlink($subpageFile);
        @rmdir($subpageDir);
        @rmdir($subpageViewsBase);
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
