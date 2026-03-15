<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Config;

use Darkheim\Infrastructure\Config\ConfigRepository;
use PHPUnit\Framework\TestCase;

class ConfigRepositoryTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/dh_config_test_' . uniqid('', true) . '/';
        mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $f) @unlink($f);
        @rmdir($this->dir);
    }

    public function testLoadExistingFile(): void
    {
        file_put_contents($this->dir . 'settings.json', json_encode(['foo' => 'bar']));
        $repo   = new ConfigRepository($this->dir);
        $result = $repo->load('settings');
        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function testLoadMissingFile(): void
    {
        $repo = new ConfigRepository($this->dir);
        $this->assertNull($repo->load('nonexistent'));
    }

    public function testLoadEmptyNameReturnsNull(): void
    {
        $repo = new ConfigRepository($this->dir);
        $this->assertNull($repo->load(''));
    }

    public function testLoadCmsOrFailSuccess(): void
    {
        $data = ['cms_installed' => true, 'language_default' => 'en'];
        file_put_contents($this->dir . 'cms.json', json_encode($data));
        $repo   = new ConfigRepository($this->dir);
        $result = $repo->loadCmsOrFail();
        $this->assertSame($data, $result);
    }

    public function testLoadCmsOrFailMissingFile(): void
    {
        $repo = new ConfigRepository($this->dir);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("doesn't exist");
        $repo->loadCmsOrFail();
    }

    public function testLoadCmsOrFailEmptyFile(): void
    {
        file_put_contents($this->dir . 'cms.json', '');
        $repo = new ConfigRepository($this->dir);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('empty');
        $repo->loadCmsOrFail();
    }

    public function testLoadDelegatesToUnderlyingReader(): void
    {
        // Verify that load() constructs the correct path and reads it
        file_put_contents($this->dir . 'custom.json', json_encode(['delegated' => true]));
        $repo   = new ConfigRepository($this->dir);
        $result = $repo->load('custom');
        $this->assertSame(['delegated' => true], $result);
    }
}

