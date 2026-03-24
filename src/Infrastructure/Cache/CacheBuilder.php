<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Cache;

/**
 * Constructs cache payloads.
 *
 * Centralises all logic previously scattered across the global functions
 * BuildCacheData(), UpdateCache(), and encodeCache().
 */
final class CacheBuilder
{
    /**
     * Builds the legacy pipe-delimited, newline-separated text cache format.
     * Columns are separated by the ¦ character; rows by \n.
     *
     * @param array<int|string, array> $dataArray
     */
    public static function buildLegacyText(array $dataArray): ?string
    {
        if (empty($dataArray)) {
            return null;
        }

        $result = '';
        foreach ($dataArray as $row) {
            $count = count($row);
            $i     = 1;
            foreach ($row as $data) {
                $result .= $data;
                if ($i < $count) {
                    $result .= '¦';
                }
                $i++;
            }
            $result .= "\n";
        }

        return $result;
    }

    /**
     * Writes a Unix timestamp header followed by $data to an existing,
     * writable file. Mirrors the legacy UpdateCache() behaviour.
     */
    public static function writeTimestamped(string $filePath, string $data): bool
    {
        if (! is_file($filePath) || ! is_writable($filePath)) {
            return false;
        }

        $fp = fopen($filePath, 'wb');
        if ($fp === false) {
            return false;
        }

        fwrite($fp, time() . "\n");
        fwrite($fp, $data);
        fclose($fp);

        return true;
    }

    /**
     * JSON-encodes $data for cache storage.
     * Pass $pretty = true to get a human-readable output (useful for debugging).
     */
    public static function encode(mixed $data, bool $pretty = false): string
    {
        $flags = JSON_THROW_ON_ERROR;
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $flags);
    }
}
