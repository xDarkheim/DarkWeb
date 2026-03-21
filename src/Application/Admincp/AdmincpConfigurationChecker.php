<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

final class AdmincpConfigurationChecker
{
    /** @var array<int,string>|null */
    private ?array $writablePaths;
    private ?bool $curlAvailable;
    private ?string $baseDir;

    /**
     * @param array<int,string>|null $writablePaths
     */
    public function __construct(?array $writablePaths = null, ?bool $curlAvailable = null, ?string $baseDir = null)
    {
        $this->writablePaths = $writablePaths;
        $this->curlAvailable = $curlAvailable;
        $this->baseDir = $baseDir;
    }

    /** @return array<int,string> */
    public function errors(): array
    {
        $configErrors = [];

        $writablePaths = $this->writablePaths ?? loadJsonFile(DARKHEIM_WRITABLE_PATHS);
        if (!is_array($writablePaths)) {
            throw new \RuntimeException('Could not load DarkCore writable paths list.');
        }

        foreach ($writablePaths as $path) {
            $thisPath = (string) $path;
            $fullPath = $this->baseDir() . ltrim($thisPath, '/');
            if (file_exists($fullPath)) {
                if (!is_writable($fullPath)) {
                    $configErrors[] = '<span style="color:#aaaaaa;">[Permission Error]</span> ' . $thisPath . ' <span style="color:red;">(file must be writable)</span>';
                }
            } else {
                $configErrors[] = '<span style="color:#aaaaaa;">[Not Found]</span> ' . $thisPath . ' <span style="color:orange;">(re-upload file)</span>';
            }
        }

        $curlAvailable = $this->curlAvailable ?? function_exists('curl_version');
        if (!$curlAvailable) {
            $configErrors[] = '<span style="color:#aaaaaa;">[PHP]</span> <span style="color:green;">cURL extension is not loaded (DarkCore requires cURL)</span>';
        }

        return $configErrors;
    }

    private function baseDir(): string
    {
        if (is_string($this->baseDir) && $this->baseDir !== '') {
            return rtrim($this->baseDir, '/') . '/';
        }

        if (defined('__ROOT_DIR__')) {
            return rtrim((string) __ROOT_DIR__, '/') . '/';
        }

        return rtrim(dirname(__DIR__, 3), '/') . '/';
    }

    public function ensureValid(): void
    {
        $errors = $this->errors();
        if ($errors !== []) {
            throw new \RuntimeException('<strong>The following errors ocurred:</strong><br /><br />' . implode('<br />', $errors));
        }
    }
}

