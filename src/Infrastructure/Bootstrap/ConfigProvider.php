<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Bootstrap;

use Darkheim\Infrastructure\Config\ConfigRepository;
use Darkheim\Infrastructure\Config\XmlConfigReader;

final class ConfigProvider
{
    private string $configDir;
    private string $moduleConfigDir;
    private string $usercpModuleConfigDir;
    private ConfigRepository $configRepository;
    private XmlConfigReader $xmlReader;
    private bool $cmsLoaded = false;

    /** @var array<string, mixed> */
    private array $cmsConfig = [];

    public function __construct(
        string $configDir,
        ?ConfigRepository $configRepository = null,
        ?XmlConfigReader $xmlReader = null,
    ) {
        $this->configDir = rtrim(str_replace('\\', '/', $configDir), '/') . '/';
        $this->moduleConfigDir = $this->configDir . 'modules/';
        $this->usercpModuleConfigDir = $this->configDir . 'modules/usercp/';
        $this->configRepository = $configRepository ?? new ConfigRepository($this->configDir);
        $this->xmlReader = $xmlReader ?? new XmlConfigReader();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function cms(): array
    {
        if (!$this->cmsLoaded) {
            $this->cmsConfig = $this->configRepository->loadCmsOrFail();
            $this->cmsLoaded = true;
        }

        return $this->cmsConfig;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function config(string $name = 'cms'): ?array
    {
        if ($name === '') {
            return null;
        }

        $result = $this->configRepository->load($name);
        if ($result !== null) {
            return $result;
        }

        $aliases = $this->configAliases();
        $alias = $aliases[$name] ?? null;
        return $alias !== null ? $this->configRepository->load($alias) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function moduleConfig(string $module): ?array
    {
        if ($module === '') {
            return null;
        }

        $candidates = [$module];
        $aliases = $this->moduleAliases();
        if (isset($aliases[$module])) {
            $candidates[] = $aliases[$module];
        }

        foreach ($candidates as $candidate) {
            if (str_contains($candidate, '/')) {
                $result = $this->xmlReader->readFile($this->moduleConfigDir . $candidate . '.xml');
                if ($result !== null) {
                    return $result;
                }
                continue;
            }

            // Check main modules/ directory first, then usercp/ subdirectory
            $result = $this->xmlReader->readFile($this->moduleConfigDir . $candidate . '.xml');
            if ($result !== null) {
                return $result;
            }

            $result = $this->xmlReader->readFile($this->usercpModuleConfigDir . $candidate . '.xml');
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function configAliases(): array
    {
        return [
            'cms' => 'config',
            'navbar' => 'navigation',
            'usercp' => 'usercp-menu',
            'castlesiege' => 'castle-siege',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function moduleAliases(): array
    {
        return [
            'forgotpassword' => 'forgot-password',
            'usercp.addstats' => 'usercp/add-stats',
            'usercp.buyzen' => 'usercp/buy-zen',
            'usercp.clearpk' => 'usercp/clear-pk',
            'usercp.clearskilltree' => 'usercp/clear-skill-tree',
            'usercp.myaccount' => 'usercp/my-account',
            'usercp.myemail' => 'usercp/my-email',
            'usercp.mypassword' => 'usercp/my-password',
            'usercp.reset' => 'usercp/reset',
            'usercp.resetstats' => 'usercp/reset-stats',
            'usercp.unstick' => 'usercp/unstick',
            'usercp.vote' => 'usercp/vote',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function globalXml(string $name): ?array
    {
        if ($name === '') {
            return null;
        }

        return $this->xmlReader->readFile($this->configDir . $name . '.xml');
    }

    public function timezone(): string
    {
        try {
            $config = $this->cms();
        } catch (\Exception) {
            $config = [];
        }
        $timezone = $config['docker_timezone'] ?? 'UTC';

        return is_string($timezone) && trim($timezone) !== '' ? $timezone : 'UTC';
    }
}

