<?php

declare(strict_types=1);

namespace Darkheim\Application\News;

use Darkheim\Infrastructure\Cache\CacheRepository;

final class NewsRepository
{
    private CacheRepository $cache;
    private string $newsDir;

    public function __construct(CacheRepository $cache, string $newsDir = '')
    {
        $this->cache   = $cache;
        $this->newsDir = $newsDir !== '' ? rtrim(str_replace('\\', '/', $newsDir), '/') . '/' : '';
    }

    /**
     * Returns all news items from cache, newest-first order (as stored).
     *
     * @return NewsItem[]
     * @throws \JsonException
     */
    public function findAll(): array
    {
        $raw = $this->cache->load('news.cache');
        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn(array $row) => $this->hydrate($row),
            $raw
        )));
    }

    public function findById(int $id): ?NewsItem
    {
        return array_find($this->findAll(), fn($item) => $item->id === $id);
    }

    /**
     * Loads rendered HTML content for an article from the file cache.
     * Mirrors the logic of legacy News::LoadCachedNews().
     */
    public function loadContent(int $id, bool $short = false, string $language = ''): string
    {
        if ($this->newsDir === '') {
            return '';
        }

        $translationsDir = $this->newsDir . 'translations/';

        // Translation cache (short)
        if ($language !== '' && $short) {
            $file = $translationsDir . 'news_' . $id . '_' . $language . '_s.cache';
            $content = $this->readFile($file);
            if ($content !== '') {
                return $content;
            }
        }

        // Translation cache (full)
        if ($language !== '') {
            $file = $translationsDir . 'news_' . $id . '_' . $language . '.cache';
            $content = $this->readFile($file);
            if ($content !== '') {
                return $content;
            }
        }

        // Short version cache
        if ($short) {
            $file = $this->newsDir . 'news_' . $id . '_s.cache';
            $content = $this->readFile($file);
            if ($content !== '') {
                return $content;
            }
        }

        // Full content cache
        return $this->readFile($this->newsDir . 'news_' . $id . '.cache');
    }

    private function readFile(string $path): string
    {
        if (!is_file($path) || !is_readable($path)) {
            return '';
        }

        $content = file_get_contents($path);

        return ($content !== false) ? $content : '';
    }

    private function hydrate(array $row): ?NewsItem
    {
        if (!isset($row['news_id'])) {
            return null;
        }

        // Cache already stores decoded plain-text titles (updateNewsCacheIndex() decodes before saving).
        $title = isset($row['news_title']) ? (string) $row['news_title'] : '';

        return new NewsItem(
            id:           (int) $row['news_id'],
            title:        $title,
            author:       $row['news_author'] ?? 'Admin',
            date:         (int) ($row['news_date'] ?? 0),
            translations: $row['translations'] ?? [],
        );
    }
}
