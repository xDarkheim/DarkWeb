<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Cache;

use Darkheim\Infrastructure\Cache\CacheManager;
use PHPUnit\Framework\TestCase;

class CacheManagerTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/dh_cm_test_' . uniqid('', true) . '/';
        mkdir($this->dir, 0o777, true);
        // Override the __PATH_CACHE__ constant is not possible once defined,
        // so we test behaviours that don't depend on it and use reflection for the rest.
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    private function manager(): CacheManager
    {
        return new CacheManager();
    }

    // ── _isProtected ─────────────────────────────────────────────────────────

    public function testIsProtectedReturnsTrueForProtectedFile(): void
    {
        $m   = new \ReflectionMethod(CacheManager::class, '_isProtected');
        $mgr = $this->manager();
        $this->assertTrue($m->invoke($mgr, 'plugins.cache'));
        $this->assertTrue($m->invoke($mgr, 'blocked_ip.cache'));
        $this->assertTrue($m->invoke($mgr, '.htaccess'));
    }

    public function testIsProtectedReturnsFalseForNormalFile(): void
    {
        $m   = new \ReflectionMethod(CacheManager::class, '_isProtected');
        $mgr = $this->manager();
        $this->assertFalse($m->invoke($mgr, 'rankings_level.cache'));
        $this->assertFalse($m->invoke($mgr, 'rankings_resets.cache'));
    }

    // ── _isJsonArrayFile ─────────────────────────────────────────────────────

    public function testIsJsonArrayFileReturnsTrueForJsonArrayFiles(): void
    {
        $m   = new \ReflectionMethod(CacheManager::class, '_isJsonArrayFile');
        $mgr = $this->manager();
        $this->assertTrue($m->invoke($mgr, 'castle_siege.cache'));
        $this->assertTrue($m->invoke($mgr, 'character_country.cache'));
        $this->assertTrue($m->invoke($mgr, 'online_characters.cache'));
    }

    public function testIsJsonArrayFileReturnsFalseForOtherFiles(): void
    {
        $m   = new \ReflectionMethod(CacheManager::class, '_isJsonArrayFile');
        $mgr = $this->manager();
        $this->assertFalse($m->invoke($mgr, 'news.cache'));
        $this->assertFalse($m->invoke($mgr, 'rankings_level.cache'));
    }

    // ── clearCacheData early-return on empty file ─────────────────────────────

    public function testClearCacheDataDoesNothingWithNoFile(): void
    {
        $mgr = $this->manager();
        // _file is null by default — should return early without exception
        $mgr->clearCacheData();
        $this->assertTrue(true); // reached without error
    }
}
