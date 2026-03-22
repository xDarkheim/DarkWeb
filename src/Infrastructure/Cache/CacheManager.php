<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Cache;

use RuntimeException;

/**
 * AdminCP cache file management — list, clear, delete profile caches.
 */
class CacheManager
{
    protected array $_protectedCacheFiles = [
        'plugins.cache',
        'blocked_ip.cache',
        'downloads.cache',
        'news.cache',
        '.htaccess',
        '.gitignore',
    ];

    protected array $_jsonArrayFiles = [
        'castle_siege.cache',
        'character_country.cache',
        'online_characters.cache',
    ];

    public $_file {
        set {
            $this->_file = $value;
        }
    }

    public function getCacheFileListAndData($type = ''): ?array
    {
        switch ($type) {
            case 'guild':
                $cacheFiles = $this->_getGuildProfileCacheFileList();
                $basePath   = __PATH_GUILD_PROFILES_CACHE__;
                break;
            case 'player':
                $cacheFiles = $this->_getPlayerProfileCacheFileList();
                $basePath   = __PATH_PLAYER_PROFILES_CACHE__;
                break;
            default:
                $cacheFiles = $this->_getCacheFileList();
                $basePath   = __PATH_CACHE__;
        }
        if (empty($cacheFiles)) return null;
        $result = [];
        foreach ($cacheFiles as $row) {
            $filePath = $basePath . $row;
            $result[] = [
                'file'  => $row,
                'size'  => filesize($filePath),
                'edit'  => date('Y/m/d H:i A', filemtime($filePath)),
                'write' => is_writable($filePath),
            ];
        }
        return $result;
    }

    public function clearCacheData(): void
    {
        if (!\Darkheim\Domain\Validator::hasValue($this->_file)) return;
        if (!in_array($this->_file, $this->_getCacheFileList(), true)) throw new RuntimeException('The requested cache file is not valid.');
        $filePath = __PATH_CACHE__ . $this->_file;
        $fileData = $this->_isJsonArrayFile($this->_file) ? '[]' : '';
        $fp = fopen($filePath, 'wb');
        if (!$fp) throw new RuntimeException('The cache file could not be open.');
        fwrite($fp, $fileData);
        fclose($fp);
    }

    public function deleteGuildCache(): void
    {
        foreach ($this->_getGuildProfileCacheFileList() as $row) {
            unlink(__PATH_GUILD_PROFILES_CACHE__ . $row);
        }
    }

    public function deletePlayerCache(): void
    {
        foreach ($this->_getPlayerProfileCacheFileList() as $row) {
            unlink(__PATH_PLAYER_PROFILES_CACHE__ . $row);
        }
    }

    protected function _getCacheFileList(): array
    {
        $dir    = opendir(__PATH_CACHE__);
        $result = [];
        while (($file = readdir($dir)) !== false) {
            if (filetype(__PATH_CACHE__ . $file) == "file" && !$this->_isProtected($file)) {
                $result[] = $file;
            }
        }
        closedir($dir);
        return $result;
    }

    protected function _getGuildProfileCacheFileList(): array
    {
        $dir    = opendir(__PATH_GUILD_PROFILES_CACHE__);
        $result = [];
        while (($file = readdir($dir)) !== false) {
            if (filetype(__PATH_GUILD_PROFILES_CACHE__ . $file) == "file" && !$this->_isProtected($file)) {
                $result[] = $file;
            }
        }
        closedir($dir);
        return $result;
    }

    protected function _getPlayerProfileCacheFileList(): array
    {
        $dir    = opendir(__PATH_PLAYER_PROFILES_CACHE__);
        $result = [];
        while (($file = readdir($dir)) !== false) {
            if (filetype(__PATH_PLAYER_PROFILES_CACHE__ . $file) == "file" && !$this->_isProtected($file)) {
                $result[] = $file;
            }
        }
        closedir($dir);
        return $result;
    }

    protected function _isProtected($file): bool
    {
        return in_array($file, $this->_protectedCacheFiles, true);
    }

    protected function _isJsonArrayFile($file): bool
    {
        return in_array($file, $this->_jsonArrayFiles, true);
    }
}

