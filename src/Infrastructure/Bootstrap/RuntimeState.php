<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Bootstrap;

final class RuntimeState
{
    /** @var array<string, mixed> */
    private array $languagePhrases = [];

    /** @var array<string, mixed> */
    private array $moduleConfig = [];

    /** @var array<string, mixed> */
    private array $customConfig = [];

    /**
     * @param array<string, mixed> $languagePhrases
     */
    public function setLanguagePhrases(array $languagePhrases): void
    {
        $this->languagePhrases = $languagePhrases;
    }

    /**
     * @param array<string, mixed> $moduleConfig
     */
    public function setModuleConfig(array $moduleConfig): void
    {
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param array<string, mixed> $customConfig
     */
    public function setCustomConfig(array $customConfig): void
    {
        $this->customConfig = $customConfig;
    }


    /**
     * @return array<string, mixed>
     */
    public function languagePhrases(): array
    {
        return $this->languagePhrases;
    }

    /**
     * @return array<string, mixed>
     */
    public function moduleConfig(): array
    {
        return $this->moduleConfig;
    }

    /**
     * @return array<string, mixed>
     */
    public function customConfig(): array
    {
        return $this->customConfig;
    }
}
