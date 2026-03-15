<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Cache;

final class CacheRepository
{
    private string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = rtrim(str_replace('\\', '/', $cacheDir), '/') . '/';
    }

    public function load(string $fileName): ?array
    {
        $path = $this->cacheDir . $fileName;

        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($data) ? $data : null;
    }

    public function save(string $fileName, string $data): bool
    {
        $path = $this->cacheDir . $fileName;

        if (!is_file($path) || !is_writable($path)) {
            return false;
        }

        $fp = fopen($path, 'wb');
        if ($fp === false) {
            return false;
        }

        fwrite($fp, $data);
        fclose($fp);

        return true;
    }

    /**
     * Reads the legacy line-based cache format (lines split by \n, columns by ¦).
     * Used by rankings and similar old-style caches.
     * Returns indexed array [ rowIndex => [ col0, col1, ... ] ] or null when empty/missing.
     */
    public function loadLegacyText(string $fileName): ?array
    {
        $path = $this->cacheDir . $fileName;

        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $result = [];
        foreach (explode("\n", $raw) as $i => $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $result[$i] = explode('¦', $trimmed);
            }
        }

        return count($result) > 0 ? $result : null;
    }
}

