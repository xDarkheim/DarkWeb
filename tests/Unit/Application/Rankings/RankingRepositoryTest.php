<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Rankings;

use Darkheim\Application\Rankings\RankingCache;
use Darkheim\Application\Rankings\RankingRepository;
use Darkheim\Infrastructure\Cache\CacheRepository;
use PHPUnit\Framework\TestCase;

class RankingRepositoryTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/dh_ranking_cache_' . uniqid(
                '',
                true
            ) . '/';
        mkdir($this->cacheDir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cacheDir . '*') ?: [] as $f) @unlink($f);
        @rmdir($this->cacheDir);
    }

    private function writeLegacy(string $file, string $content): RankingRepository
    {
        file_put_contents($this->cacheDir . $file, $content);
        return new RankingRepository(new CacheRepository($this->cacheDir));
    }

    private function writeJson(string $file, array $data): RankingRepository
    {
        file_put_contents($this->cacheDir . $file, json_encode($data));
        return new RankingRepository(new CacheRepository($this->cacheDir));
    }

    // ── load ──────────────────────────────────────────────────────────────────

    public function testLoadReturnsCacheObject(): void
    {
        $repo   = $this->writeLegacy('rankings_level.cache', "1700000000\nPlayer1¦400¦1\nPlayer2¦350¦2\n");
        $result = $repo->load('rankings_level.cache');

        $this->assertInstanceOf(RankingCache::class, $result);
        $this->assertSame(1700000000, $result->timestamp);
        $this->assertCount(2, $result->entries);
        $this->assertSame(['Player1', '400', '1'], $result->entries[0]);
    }

    public function testLoadReturnsNullWhenFileMissing(): void
    {
        $repo = new RankingRepository(new CacheRepository($this->cacheDir));
        $this->assertNull($repo->load('rankings_level.cache'));
    }

    public function testLoadReturnsNullWhenFileEmpty(): void
    {
        $repo = $this->writeLegacy('rankings_level.cache', '');
        $this->assertNull($repo->load('rankings_level.cache'));
    }

    // ── loadCharacterCountries ────────────────────────────────────────────────

    public function testLoadCharacterCountriesReturnsArray(): void
    {
        $data = ['Player1' => 'UA', 'Player2' => 'US'];
        $repo = $this->writeJson('character_country.cache', $data);
        $this->assertSame($data, $repo->loadCharacterCountries());
    }

    public function testLoadCharacterCountriesReturnsEmptyWhenMissing(): void
    {
        $repo = new RankingRepository(new CacheRepository($this->cacheDir));
        $this->assertSame([], $repo->loadCharacterCountries());
    }

    public function testLoadCharacterCountriesReturnsEmptyWhenMalformedJson(): void
    {
        file_put_contents($this->cacheDir . 'character_country.cache', '{not json}');
        $repo = new RankingRepository(new CacheRepository($this->cacheDir));
        $this->assertSame([], $repo->loadCharacterCountries());
    }

    // ── loadOnlineCharacters ──────────────────────────────────────────────────

    public function testLoadOnlineCharactersReturnsArray(): void
    {
        $data = ['Player1', 'Player2'];
        $repo = $this->writeJson('online_characters.cache', $data);
        $this->assertSame($data, $repo->loadOnlineCharacters());
    }

    public function testLoadOnlineCharactersReturnsEmptyWhenMalformedJson(): void
    {
        file_put_contents($this->cacheDir . 'online_characters.cache', '{not json}');
        $repo = new RankingRepository(new CacheRepository($this->cacheDir));
        $this->assertSame([], $repo->loadOnlineCharacters());
    }
}

