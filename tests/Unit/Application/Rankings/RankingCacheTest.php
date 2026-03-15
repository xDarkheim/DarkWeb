<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Rankings;

use Darkheim\Application\Rankings\RankingCache;
use PHPUnit\Framework\TestCase;

class RankingCacheTest extends TestCase
{
    public function testConstructorStoresValues(): void
    {
        $entries = [['Player1', '400', '1'], ['Player2', '350', '2']];
        $cache   = new RankingCache(1700000000, $entries);

        $this->assertSame(1700000000, $cache->timestamp);
        $this->assertSame($entries, $cache->entries);
    }

    public function testEmptyEntries(): void
    {
        $cache = new RankingCache(0, []);
        $this->assertSame(0, $cache->timestamp);
        $this->assertSame([], $cache->entries);
    }
}

