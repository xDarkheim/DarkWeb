<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\LegacyModuleAdapter;
use PHPUnit\Framework\TestCase;

final class LegacyModuleAdapterTest extends TestCase
{
    private LegacyModuleAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new LegacyModuleAdapter();
    }

    public function testLoadModuleReturnsFalseForMissingModule(): void
    {
        $this->assertFalse($this->adapter->loadModule('missing_module_for_test'));
    }

    public function testLoadModuleIncludesExistingModuleFile(): void
    {
        $moduleName = '__route_test_module';
        $modulePath = __PATH_MODULES__ . $moduleName . '.php';
        file_put_contents($modulePath, "<?php \$GLOBALS['__legacy_module_loaded'] = true;\n");

        $GLOBALS['__legacy_module_loaded'] = false;
        $this->assertTrue($this->adapter->loadModule($moduleName));
        $this->assertTrue((bool) $GLOBALS['__legacy_module_loaded']);

        @unlink($modulePath);
    }

    public function testLoadSubModuleIncludesExistingSubModuleFile(): void
    {
        $page = '__route_test';
        $sub = 'sub';
        $dir = __PATH_MODULES__ . $page;
        @mkdir($dir, 0777, true);
        $modulePath = $dir . '/' . $sub . '.php';
        file_put_contents($modulePath, "<?php \$GLOBALS['__legacy_submodule_loaded'] = true;\n");

        $GLOBALS['__legacy_submodule_loaded'] = false;
        $this->assertTrue($this->adapter->loadSubModule($page, $sub));
        $this->assertTrue((bool) $GLOBALS['__legacy_submodule_loaded']);

        @unlink($modulePath);
        @rmdir($dir);
    }
}

