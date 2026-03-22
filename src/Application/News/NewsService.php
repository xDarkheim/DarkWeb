<?php

declare(strict_types=1);

namespace Darkheim\Application\News;

use Darkheim\Application\Language\Translator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Cache\CacheBuilder;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Application\Language\LanguageRepository;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;

/**
 * NewsService — full CRUD, caching, translation management for news articles.
 * Complete PSR-4 port of the legacy `News` classmap class.
 */
class NewsService
{
    private string $_configFile         = 'news';
    private bool $_enableShortNews;
    private int $_shortNewsCharLimit;

    private $_id;
    private $_language;
    private $_title;
    private $_content;

    protected $db;

    public function __construct()
    {
        $config = BootstrapContext::configProvider()?->moduleConfig($this->_configFile);
        $this->_enableShortNews    = (bool) ($config['news_short'] ?? false);
        $this->_shortNewsCharLimit = (int)  ($config['news_short_char_limit'] ?? 100);
    }

    // ─── Setters ─────────────────────────────────────────────────────────────

    public function setId($id): void
    {
        if (!Validator::UnsignedNumber($id)) return;
        $this->_id = $id;
    }

    public function setLanguage($language): void
    {
        if (!Validator::hasValue($language)) return;
        $languagesList = LanguageRepository::getInstalled();
        if (!is_array($languagesList)) return;
        if (!in_array($language, $languagesList, true)) return;
        $this->_language = $language;
    }

    public function setTitle($title): void
    {
        if (!Validator::hasValue($title)) return;
        $this->_title = $title;
    }

    public function setContent($content): void
    {
        if (!Validator::hasValue($content)) return;
        $this->_content = $content;
    }

    // ─── Write operations ─────────────────────────────────────────────────────

    public function addNews($title, $content, $author = 'Administrator', $comments = 1): void
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($title) || !Validator::hasValue($content) || !Validator::hasValue($author)) {
            \Darkheim\Application\View\MessageRenderer::toast('error', Translator::phrase('error_41'));
            return;
        }
        if (!$this->checkTitle($title)) { \Darkheim\Application\View\MessageRenderer::toast('error', Translator::phrase('error_42')); return; }
        if (!$this->checkContent($content)) { \Darkheim\Application\View\MessageRenderer::toast('error', Translator::phrase('error_43')); return; }

        if ($comments < 0 || $comments > 1) $comments = 1;

        // Ensure UTF-8 encoding before storing
        $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');

        $add = $this->db->query(
            "INSERT INTO " . \News . " (news_title, news_author, news_date, news_content, allow_comments) VALUES (?, ?, ?, ?, ?)",
            [base64_encode($title), $author, time(), base64_encode($content), $comments]
        );

        \Darkheim\Application\View\MessageRenderer::toast($add ? 'success' : 'error', $add ? Translator::phrase('success_15') : Translator::phrase('error_23'));
    }

    public function removeNews($id): bool
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::Number($id)) return false;
        if (!$this->newsIdExists($id)) return false;

        $remove = $this->db->query("DELETE FROM " . \News . " WHERE news_id = ?", [$id]);
        if (!$remove) return false;

        $this->setId($id);
        $this->_deleteAllNewsTranslations();
        return true;
    }

    public function editNews($id, $title, $content, $author, $comments, $date): void
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($id) || !Validator::hasValue($title) || !Validator::hasValue($content) || !Validator::hasValue($author) || !Validator::hasValue($comments) || !Validator::hasValue($date)) return;
        if (!$this->newsIdExists($id)) return;
        if (!$this->checkTitle($title) || !$this->checkContent($content)) return;

        // Ensure UTF-8 encoding before storing
        $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');

        $query = $this->db->query(
            "UPDATE " . \News . " SET news_title = ?, news_content = ?, news_author = ?, news_date = ?, allow_comments = ? WHERE news_id = ?",
            [base64_encode($title), base64_encode($content), $author, strtotime($date), $comments, $id]
        );

        \Darkheim\Application\View\MessageRenderer::toast($query ? 'success' : 'error', $query ? 'News successfully edited.' : Translator::phrase('error_99'));
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    public function checkTitle($title): bool
    {
        return Validator::hasValue($title) && strlen($title) >= 4 && strlen($title) <= 255;
    }

    public function checkContent($content): bool
    {
        return Validator::hasValue($content) && strlen($content) >= 4;
    }

    // ─── Read / cache operations ───────────────────────────────────────────────

    public function retrieveNews(): ?array
    {
        $this->db = Connection::Database('MuOnline');
        $news = $this->db->query_fetch("SELECT * FROM " . \News . " ORDER BY news_id DESC");
        if (!is_array($news)) return null;

        foreach ($news as $id => $data) {
            $news[$id]['news_title']   = base64_decode($data['news_title']);
            $news[$id]['news_content'] = base64_decode($data['news_content']);
        }
        return $news;
    }

    public function newsIdExists($id): bool
    {
        if (!Validator::UnsignedNumber($id)) return false;
        $cachedNews = (new CacheRepository(__PATH_CACHE__))->load('news.cache');
        if (!is_array($cachedNews)) return false;

        return array_any(
            $cachedNews,
            fn($cacheData) => $cacheData['news_id'] == $id
        );
    }

    public function deleteNewsFiles(): void
    {
        $files = glob(__PATH_NEWS_CACHE__ . '*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    public function cacheNews(): bool
    {
        if (!$this->isNewsDirWritable()) return false;

        $news_list = $this->retrieveNews();
        $this->deleteNewsFiles();
        if (!is_array($news_list)) return false;

        foreach ($news_list as $news) {
            $handle = fopen(__PATH_NEWS_CACHE__ . 'news_' . $news['news_id'] . '.cache',
                'ab'
            );
            fwrite($handle, $news['news_content']);
            fclose($handle);

            if ($this->_enableShortNews) {
                $handle2 = fopen(__PATH_NEWS_CACHE__ . 'news_' . $news['news_id'] . '_s.cache',
                    'ab'
                );
                fwrite($handle2, $this->_getShortVersion($news['news_content']));
                fclose($handle2);
            }
        }
        return true;
    }

    public function isNewsDirWritable(): bool
    {
        return is_writable(__PATH_NEWS_CACHE__);
    }

    public function retrieveNewsDataForCache(): ?array
    {
        $this->db = Connection::Database('MuOnline');
        $news = $this->db->query_fetch(
            "SELECT news_id, news_title, news_author, news_date, allow_comments, news_content FROM " . \News . " ORDER BY news_id DESC"
        );
        return is_array($news) ? $news : null;
    }

    public function updateNewsCacheIndex(): bool
    {
        $newsList = $this->retrieveNewsDataForCache();
        if (!is_array($newsList)) {
            (new CacheRepository(__PATH_CACHE__))->save('news.cache', '');
            return true;
        }

        foreach ($newsList as $key => $row) {
            $this->setId($row['news_id']);
            $newsList[$key]['news_title']   = base64_decode($row['news_title']);
            $newsList[$key]['news_content'] = base64_decode($row['news_content']);
            $newsTranslations = $this->getNewsTranslationsDataList();
            if (!is_array($newsTranslations)) continue;
            foreach ($newsTranslations as $translation) {
                $newsList[$key]['translations'][$translation['news_language']] = $translation['news_title'];
            }
        }

        $encoded = CacheBuilder::encode($newsList);

        return (new CacheRepository(__PATH_CACHE__))->save('news.cache', $encoded);
    }

    public function loadNewsData($id)
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($id) || !$this->newsIdExists($id)) return;
        $query = $this->db->query_fetch_single("SELECT * FROM " . \News . " WHERE news_id = ?", [$id]);
        if (!is_array($query)) return;
        $query['news_title']   = base64_decode($query['news_title']);
        $query['news_content'] = base64_decode($query['news_content']);
        return $query;
    }

    // ─── Translation operations ───────────────────────────────────────────────

    public function getNewsTranslations(): ?array
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($this->_id)) return null;
        $rows = $this->db->query_fetch("SELECT * FROM " . \News_Translations . " WHERE news_id = ?", [$this->_id]);
        if (!is_array($rows)) return null;
        $result = [];
        foreach ($rows as $t) { $result[] = $t['news_language']; }
        return count($result) > 0 ? $result : null;
    }

    public function addNewsTransation(): void
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($this->_id))       throw new \Exception('The provided news id is not valid.');
        if (!Validator::hasValue($this->_language)) throw new \Exception('The provided news language is not valid.');
        if (!Validator::hasValue($this->_title))    throw new \Exception('The provided news title is not valid.');
        if (!Validator::hasValue($this->_content))  throw new \Exception('The provided news content is not valid.');

        $existing = $this->getNewsTranslations();
        if (is_array($existing) && in_array($this->_language, $existing, true)) {
            throw new \Exception('A translation for this language already exists, please use the edit news translation module.');
        }

        $result = $this->db->query(
            "INSERT INTO " . \News_Translations . " (news_id, news_language, news_title, news_content) VALUES (?, ?, ?, ?)",
            [$this->_id, $this->_language, base64_encode($this->_title), base64_encode($this->_content)]
        );
        if (!$result) throw new \Exception('Could not add the news translation.');

        $this->_writeTranslationCache();
    }

    public function updateNewsTransation(): void
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($this->_id))       throw new \Exception('The provided news id is not valid.');
        if (!Validator::hasValue($this->_language)) throw new \Exception('The provided news language is not valid.');
        if (!Validator::hasValue($this->_title))    throw new \Exception('The provided news title is not valid.');
        if (!Validator::hasValue($this->_content))  throw new \Exception('The provided news content is not valid.');

        $result = $this->db->query(
            "UPDATE " . \News_Translations . " SET news_title = ?, news_content = ? WHERE news_id = ? AND news_language = ?",
            [base64_encode($this->_title), base64_encode($this->_content), $this->_id, $this->_language]
        );
        if (!$result) throw new \Exception('Could not update the news translation.');

        $this->_writeTranslationCache();
    }

    public function deleteNewsTranslation(): void
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($this->_id))       throw new \Exception('The provided news id is not valid.');
        if (!Validator::hasValue($this->_language)) throw new \Exception('The provided news language is not valid.');

        $result = $this->db->query(
            "DELETE FROM " . \News_Translations . " WHERE news_id = ? AND news_language = ?",
            [$this->_id, $this->_language]
        );
        if (!$result) throw new \Exception('Could not delete news translation.');

        foreach ($this->_translationCachePaths() as $path) {
            if (file_exists($path)) unlink($path);
        }
    }

    public function loadNewsTranslationData()
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($this->_id) || !Validator::hasValue($this->_language)) return;
        $result = $this->db->query_fetch_single(
            "SELECT * FROM " . \News_Translations . " WHERE news_id = ? AND news_language = ?",
            [$this->_id, $this->_language]
        );
        return is_array($result) ? $result : null;
    }

    public function getNewsTranslationsDataList(): ?array
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($this->_id)) return null;
        $result = $this->db->query_fetch("SELECT * FROM " . \News_Translations . " WHERE news_id = ?", [$this->_id]);
        return is_array($result) ? $result : null;
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function _deleteAllNewsTranslations(): void
    {
        $this->db = Connection::Database('MuOnline');
        if (!Validator::hasValue($this->_id)) {
            return;
        }
        $translations = $this->getNewsTranslations();
        if (!is_array($translations)) {
            return;
        }
        foreach ($translations as $translation) {
            try {
                $this->setLanguage($translation);
                $this->deleteNewsTranslation();
            } catch (\Exception $ex) {
                continue;
            }
        }
    }

    private function _writeTranslationCache(): void
    {
        [$file, $fileShort] = $this->_translationCachePaths();

        $handle = fopen($file, 'wb');
        fwrite($handle, $this->_content);
        fclose($handle);

        if ($this->_enableShortNews) {
            $handle2 = fopen($fileShort, 'wb');
            fwrite($handle2, $this->_getShortVersion($this->_content));
            fclose($handle2);
        }
    }

    private function _translationCachePaths(): array
    {
        $base = __PATH_NEWS_TRANSLATIONS_CACHE__ . 'news_' . $this->_id . '_' . $this->_language;
        return [$base . '.cache', $base . '_s.cache'];
    }

    private function _getShortVersion(string $newsData): string
    {
        $value = html_entity_decode($newsData);
        if (mb_strwidth($value, 'UTF-8') <= $this->_shortNewsCharLimit) return $value;
        do {
            $len          = mb_strwidth($value, 'UTF-8');
            $len_stripped = mb_strwidth(strip_tags($value), 'UTF-8');
            $value        = mb_strimwidth($value, 0, $this->_shortNewsCharLimit + ($len - $len_stripped), '', 'UTF-8');
        } while ($len_stripped > $this->_shortNewsCharLimit);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $value, LIBXML_HTML_NODEFDTD);
        $value = $dom->saveHtml($dom->getElementsByTagName('body')->item(0));
        $value = mb_strimwidth($value, 6, mb_strwidth($value, 'UTF-8') - 13, '', 'UTF-8');
        return preg_replace('/<(\w+)\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\s*>/', '', $value);
    }
}

