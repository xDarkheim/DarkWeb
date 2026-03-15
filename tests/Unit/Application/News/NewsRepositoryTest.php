<?php

declare(strict_types=1);

namespace Tests\Unit\Application\News;

use Darkheim\Application\News\NewsRepository;
use Darkheim\Application\News\NewsItem;
use Darkheim\Infrastructure\Cache\CacheRepository;
use PHPUnit\Framework\TestCase;

class NewsRepositoryTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/dh_news_cache_' . uniqid(
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

    private function makeRepo(?array $cacheData): NewsRepository
    {
        $cacheFile = $this->cacheDir . 'news.cache';
        file_put_contents($cacheFile, $cacheData !== null ? json_encode($cacheData) : '');
        return new NewsRepository(new CacheRepository($this->cacheDir));
    }

    // ── findAll ───────────────────────────────────────────────────────────────

    public function testFindAllReturnsEmptyArrayWhenCacheEmpty(): void
    {
        $repo = $this->makeRepo(null);
        $this->assertSame([], $repo->findAll());
    }

    /**
     * Cache stores plain-text titles (updateNewsCacheIndex decodes base64 before saving).
     * hydrate() reads them as-is — no second decode.
     */
    public function testFindAllHydratesItems(): void
    {
        $raw = [[
            'news_id'      => '5',
            'news_title'   => 'Hello World',   // plain text — already decoded in cache
            'news_author'  => 'Editor',
            'news_date'    => '1700000000',
            'translations' => [],
        ]];
        $repo  = $this->makeRepo($raw);
        $items = $repo->findAll();
        $this->assertCount(1, $items);
        $this->assertInstanceOf(NewsItem::class, $items[0]);
        $this->assertSame(5, $items[0]->id);
        $this->assertSame('Hello World', $items[0]->title);
        $this->assertSame('Editor', $items[0]->author);
    }

    public function testFindAllSkipsRowsMissingNewsId(): void
    {
        $repo = $this->makeRepo([['news_title' => 'No ID']]);   // plain text
        $this->assertSame([], $repo->findAll());
    }

    // ── findById ──────────────────────────────────────────────────────────────

    public function testFindByIdFound(): void
    {
        $raw = [
            ['news_id' => '1', 'news_title' => 'First'],    // plain text
            ['news_id' => '2', 'news_title' => 'Second'],   // plain text
        ];
        $repo = $this->makeRepo($raw);
        $item = $repo->findById(2);
        $this->assertNotNull($item);
        $this->assertSame(2, $item->id);
    }

    public function testFindByIdNotFound(): void
    {
        $repo = $this->makeRepo([['news_id' => '1', 'news_title' => 'First']]);  // plain text
        $this->assertNull($repo->findById(99));
    }

    // ── loadContent ───────────────────────────────────────────────────────────

    public function testLoadContentEmptyNewsDirReturnsEmpty(): void
    {
        $repo = new NewsRepository(new CacheRepository($this->cacheDir), '');
        $this->assertSame('', $repo->loadContent(1));
    }

    public function testLoadContentReadsFullCacheFile(): void
    {
        $dir = sys_get_temp_dir() . '/dh_news_content_' . uniqid('', true) . '/';
        mkdir($dir, 0777, true);
        file_put_contents($dir . 'news_3.cache', '<p>Article content</p>');

        $repo    = new NewsRepository(new CacheRepository($this->cacheDir), $dir);
        $content = $repo->loadContent(3);
        $this->assertSame('<p>Article content</p>', $content);

        @unlink($dir . 'news_3.cache');
        @rmdir($dir);
    }

    public function testLoadContentReadsShortCacheFile(): void
    {
        $dir = sys_get_temp_dir() . '/dh_news_content_' . uniqid('', true) . '/';
        mkdir($dir, 0777, true);
        file_put_contents($dir . 'news_4_s.cache', 'Short excerpt');

        $repo    = new NewsRepository(new CacheRepository($this->cacheDir), $dir);
        $content = $repo->loadContent(4, true);
        $this->assertSame('Short excerpt', $content);

        @unlink($dir . 'news_4_s.cache');
        @rmdir($dir);
    }

    public function testLoadContentTranslationTakesPriority(): void
    {
        $dir   = sys_get_temp_dir() . '/dh_news_content_' . uniqid('', true) . '/';
        $trans = $dir . 'translations/';
        mkdir($trans, 0777, true);
        file_put_contents($trans . 'news_5_fr.cache', 'Version française');
        file_put_contents($dir . 'news_5.cache', 'Default content');

        $repo    = new NewsRepository(new CacheRepository($this->cacheDir), $dir);
        $content = $repo->loadContent(5, false, 'fr');
        $this->assertSame('Version française', $content);

        @unlink($trans . 'news_5_fr.cache');
        @unlink($dir . 'news_5.cache');
        @rmdir($trans);
        @rmdir($dir);
    }
}

