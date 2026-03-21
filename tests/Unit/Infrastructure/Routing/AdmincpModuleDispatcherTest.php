<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\AdmincpModuleDispatcher;
use PHPUnit\Framework\TestCase;

final class AdmincpModuleDispatcherTest extends TestCase
{
    public function testDispatchReturnsFalseForMissingModule(): void
    {
        $dispatcher = new AdmincpModuleDispatcher();
        $this->assertFalse($dispatcher->dispatch('missing_admin_module'));
    }

    public function testDispatchIncludesExistingModuleFile(): void
    {
        $moduleName = '__admin_route_test';
        $modulePath = __PATH_ADMINCP_MODULES__ . $moduleName . '.php';
        file_put_contents($modulePath, "<?php \$GLOBALS['__admin_module_dispatched'] = true;\n");

        $GLOBALS['__admin_module_dispatched'] = false;

        $dispatcher = new AdmincpModuleDispatcher();
        $this->assertTrue($dispatcher->dispatch($moduleName));
        $this->assertTrue((bool) $GLOBALS['__admin_module_dispatched']);

        @unlink($modulePath);
    }
}

