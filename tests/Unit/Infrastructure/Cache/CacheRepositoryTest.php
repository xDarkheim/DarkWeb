<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Cache;

use Darkheim\Infrastructure\Cache\CacheRepository;
use PHPUnit\Framework\TestCase;

class CacheRepositoryTest extends TestCase
{
    private string $dir;
    private CacheRepository $repo;

    protected function setUp(): void
    {
        $this->dir  = sys_get_temp_dir() . '/dh_cache_test_' . uniqid() . '/';
        mkdir($this->dir, 0777, true);
        $this->repo = new CacheRepository($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    // ── load() ───────────────────────────────────────────────────────────────

    public function testLoadValidJson(): void
    {
        file_put_contents($this->dir . 'test.cache', json_encode([['key' => 'val']]));
        $result = $this->repo->load('test.cache');
        $this->assertSame([['key' => 'val']], $result);
    }

    public function testLoadMissingFile(): void
    {
        $this->assertNull($this->repo->load('nonexistent.cache'));
    }

    public function testLoadEmptyFile(): void
    {
        file_put_contents($this->dir . 'empty.cache', '');
        $this->assertNull($this->repo->load('empty.cache'));
    }

    public function testLoadMalformedJson(): void
    {
        file_put_contents($this->dir . 'bad.cache', '{not json}');
        $this->assertNull($this->repo->load('bad.cache'));
    }

    // ── save() ───────────────────────────────────────────────────────────────

    public function testSaveWritesToFile(): void
    {
        file_put_contents($this->dir . 'save.cache', '');
        $result = $this->repo->save('save.cache', 'hello');
        $this->assertTrue($result);
        $this->assertSame('hello', file_get_contents($this->dir . 'save.cache'));
    }

    public function testSaveNonExistentFileReturnsFalse(): void
    {
        $this->assertFalse($this->repo->save('missing.cache', 'data'));
    }

    // ── loadLegacyText() ─────────────────────────────────────────────────────

    public function testLoadLegacyTextParsesCorrectly(): void
    {
        $content = "1700000000\nPlayer1¦400¦1\nPlayer2¦350¦2\n";
        file_put_contents($this->dir . 'rankings.cache', $content);
        $result = $this->repo->loadLegacyText('rankings.cache');
        $this->assertIsArray($result);
        $this->assertSame(['1700000000'], $result[0]);
        $this->assertSame(['Player1', '400', '1'], $result[1]);
        $this->assertSame(['Player2', '350', '2'], $result[2]);
    }

    public function testLoadLegacyTextMissingFile(): void
    {
        $this->assertNull($this->repo->loadLegacyText('nonexistent.cache'));
    }

    public function testLoadLegacyTextEmptyFile(): void
    {
        file_put_contents($this->dir . 'empty.cache', '');
        $this->assertNull($this->repo->loadLegacyText('empty.cache'));
    }
}

