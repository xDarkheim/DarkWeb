<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\Database\Connection;

final class DownloadLinkService
{
    /** @return array<int,array<string,mixed>>|null */
    public function all(): ?array
    {
        $db = Connection::Database('MuOnline');
        $result = $db->query_fetch('SELECT * FROM ' . Downloads . ' ORDER BY download_type, download_id');

        return is_array($result) ? $result : null;
    }

    public function add(string $title, string $link, string $description = '', string|int|float $size = 0, string|int $type = 1): bool
    {
        if (!$this->isValidPayload($title, $link, $description, $size, $type)) {
            return false;
        }

        $db = Connection::Database('MuOnline');
        $result = $db->query(
            'INSERT INTO ' . Downloads . ' (download_title, download_description, download_link, download_size, download_type) VALUES (?, ?, ?, ?, ?)',
            [$title, $description, $link, $size, $type]
        );

        if (!$result) {
            return false;
        }

        return $this->updateCache();
    }

    public function edit(string|int $id, string $title, string $link, string $description = '', string|int|float $size = 0, string|int $type = 1): bool
    {
        if (!check_value($id) || !$this->isValidPayload($title, $link, $description, $size, $type)) {
            return false;
        }

        $db = Connection::Database('MuOnline');
        $result = $db->query(
            'UPDATE ' . Downloads . ' SET download_title = ?, download_description = ?, download_link = ?, download_size = ?, download_type = ? WHERE download_id = ?',
            [$title, $description, $link, $size, $type, $id]
        );

        if (!$result) {
            return false;
        }

        return $this->updateCache();
    }

    public function delete(string|int $id): bool
    {
        if (!check_value($id)) {
            return false;
        }

        $db = Connection::Database('MuOnline');
        $result = $db->query('DELETE FROM ' . Downloads . ' WHERE download_id = ?', [$id]);

        if (!$result) {
            return false;
        }

        return $this->updateCache();
    }

    public function updateCache(): bool
    {
        $downloadsData = $this->all();
        $cacheData = encodeCache($downloadsData);
        updateCacheFile('downloads.cache', $cacheData);

        return true;
    }

    private function isValidPayload(string $title, string $link, string $description, string|int|float $size, string|int $type): bool
    {
        if (!check_value($title) || !check_value($link) || !check_value((string) $size) || !check_value((string) $type)) {
            return false;
        }

        if (strlen($title) > 100 || strlen($description) > 100) {
            return false;
        }

        return true;
    }
}

