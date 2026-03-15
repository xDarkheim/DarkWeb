<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Plugins;

use Darkheim\Infrastructure\Plugins\Plugins;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tests\Stubs\DbTestHelper;

class PluginsTest extends TestCase
{
    use DbTestHelper;

    private function make(dB $mockDb): Plugins
    {
        /** @var Plugins $sut */
        $sut = $this->makeWithDb(Plugins::class, $mockDb, 'db');
        return $sut;
    }

    // ── checkXML (private) ───────────────────────────────────────────────────

    private function callCheckXml(Plugins $sut, array $data): bool
    {
        $m = new ReflectionMethod(Plugins::class, 'checkXML');
        return $m->invoke($sut, $data);
    }

    public function testCheckXmlReturnsTrueForValidData(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $data = [
            'name'          => 'TestPlugin',
            'author'        => 'Dev',
            'version'       => '1.0',
            'compatibility' => ['darkheim' => '0.0.1'],
            'folder'        => 'test_plugin',
            'files'         => ['file' => 'main.php'],
        ];
        $this->assertTrue($this->callCheckXml($sut, $data));
    }

    public function testCheckXmlReturnsFalseForMissingKey(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $data = [
            'name'    => 'TestPlugin',
            'author'  => 'Dev',
            // missing version, compatibility, folder, files
        ];
        $this->assertFalse($this->callCheckXml($sut, $data));
    }

    public function testCheckXmlReturnsFalseForEmptyName(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $data = [
            'name'          => '',
            'author'        => 'Dev',
            'version'       => '1.0',
            'compatibility' => ['darkheim' => '0.0.1'],
            'folder'        => 'test_plugin',
            'files'         => ['file' => 'main.php'],
        ];
        $this->assertFalse($this->callCheckXml($sut, $data));
    }

    // ── checkCompatibility (private) ─────────────────────────────────────────

    private function callCheckCompatibility(Plugins $sut, array $data): bool
    {
        $m = new ReflectionMethod(Plugins::class, 'checkCompatibility');
        return $m->invoke($sut, $data);
    }

    public function testCheckCompatibilityMatchesCurrentVersion(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->assertTrue($this->callCheckCompatibility($sut, ['darkheim' => __CMS_VERSION__]));
    }

    public function testCheckCompatibilityMatchesInArray(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->assertTrue($this->callCheckCompatibility($sut, ['darkheim' => [__CMS_VERSION__, '0.0.2']]));
    }

    public function testCheckCompatibilityReturnsFalseForWrongVersion(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->assertFalse($this->callCheckCompatibility($sut, ['darkheim' => '99.99.99']));
    }

    public function testCheckCompatibilityReturnsFalseForMissingKey(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->assertFalse($this->callCheckCompatibility($sut, []));
    }

    // ── retrieveInstalledPlugins ──────────────────────────────────────────────

    public function testRetrieveInstalledPluginsReturnsArray(): void
    {
        $rows = [['id' => 1, 'name' => 'TestPlugin', 'status' => 1]];
        $db   = $this->createMock(dB::class);
        $db->method('query_fetch')->willReturn($rows);
        $sut = $this->make($db);
        $this->assertSame($rows, $sut->retrieveInstalledPlugins());
    }

    // ── uninstallPlugin ───────────────────────────────────────────────────────

    public function testUninstallPluginReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);
        $this->assertTrue($sut->uninstallPlugin(1));
    }
}

