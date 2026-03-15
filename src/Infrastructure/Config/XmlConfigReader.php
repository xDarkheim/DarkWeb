<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Config;

final class XmlConfigReader
{
    public function readFile(string $path): ?array
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $xml = @simplexml_load_string($raw);
        if ($xml === false) {
            return null;
        }

        $decoded = json_decode(
            json_encode($xml->children(), JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return is_array($decoded) ? $decoded : null;
    }
}

