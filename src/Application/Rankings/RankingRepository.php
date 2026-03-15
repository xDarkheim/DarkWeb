<?php

declare(strict_types=1);

namespace Darkheim\Application\Rankings;

use Darkheim\Infrastructure\Cache\CacheRepository;

final class RankingRepository
{
    private CacheRepository $cache;

    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Loads a legacy text ranking cache.
     * Row 0 is stripped as metadata (contains timestamp); the rest become $entries.
     */
    public function load(string $cacheFile): ?RankingCache
    {
        $raw = $this->cache->loadLegacyText($cacheFile);
        if (!is_array($raw)) {
            return null;
        }

        $timestamp = isset($raw[0][0]) ? (int) $raw[0][0] : 0;

        $entries = [];
        foreach ($raw as $i => $row) {
            if ($i >= 1) {
                $entries[] = $row;
            }
        }

        return new RankingCache($timestamp, $entries);
    }

    /** @return array<string, string>  charName => countryCode */
    public function loadCharacterCountries(): array
    {
        return $this->cache->load('character_country.cache') ?? [];
    }

    /** @return string[]  list of online character names */
    public function loadOnlineCharacters(): array
    {
        return $this->cache->load('online_characters.cache') ?? [];
    }
}

