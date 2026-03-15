<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Config;

use Exception;

final class ConfigRepository
{
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
     * Loads cms.json and preserves the exact legacy exception messages.
     *
     * @throws Exception
     */
    public function loadCmsOrFail(): array
    {
        $path = $this->configDir . 'cms.json';

        if (!is_file($path)) {
            throw new Exception("Darkheim's configuration file doesn't exist, please reupload the website files.");
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            throw new Exception("Darkheim's configuration file is empty, please run the installation script.");
        }

        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return is_array($data) ? $data : [];
    }
}

