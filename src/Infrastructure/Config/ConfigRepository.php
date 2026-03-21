<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Config;

use Exception;

final class ConfigRepository
{
    /** @var array<int,string> */
    private const REMOVED_CMS_KEYS = [
        'cron_api',
        'cron_api_key',
    ];

    private string $configDir;
    private JsonConfigReader $reader;

    public function __construct(string $configDir, ?JsonConfigReader $reader = null)
    {
        $this->configDir = rtrim(str_replace('\\', '/', $configDir), '/') . '/';
        $this->reader    = $reader ?? new JsonConfigReader();
    }

    public function load(string $name): ?array
    {
        if ($name === '') {
            return null;
        }

        return $this->reader->readFile($this->configDir . $name . '.json');
    }

    /**
     * Loads config.json and preserves the exact legacy exception messages.
     *
     * @throws Exception
     */
    public function loadCmsOrFail(): array
    {
        $path = $this->configDir . 'config.json';

        if (!is_file($path)) {
            throw new Exception("Darkheim's configuration file doesn't exist, please reupload the website files.");
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            throw new Exception("Darkheim's configuration file is empty, please run the installation script.");
        }

        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return is_array($data) ? self::sanitizeCms($data) : [];
    }

    /**
     * @param array<string,mixed> $data
     * @throws Exception
     */
    public function saveCms(array $data): void
    {
        $path = $this->configDir . 'config.json';
        $encoded = json_encode(self::sanitizeCms($data), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new Exception('Could not encode DarkCore configurations.');
        }

        $written = file_put_contents($path, $encoded, LOCK_EX);
        if ($written === false) {
            throw new Exception('Could not save configuration file.');
        }
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public static function sanitizeCms(array $data): array
    {
        foreach (self::REMOVED_CMS_KEYS as $key) {
            unset($data[$key]);
        }

        return $data;
    }
}

