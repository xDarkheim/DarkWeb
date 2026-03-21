<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Bootstrap;

use Darkheim\Infrastructure\Bootstrap\ConfigProvider;
use Darkheim\Infrastructure\Bootstrap\RuntimeState;
use Darkheim\Infrastructure\Bootstrap\TimezoneInitializer;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    private string $configDir;
    private string $originalTimezone;

    protected function setUp(): void
    {
        $this->configDir = sys_get_temp_dir() . '/darkcore_bootstrap_' . uniqid('', true) . '/config/';
        mkdir($this->configDir . 'modules/', 0777, true);
        $this->originalTimezone = date_default_timezone_get();
        $state = new RuntimeState();
        $state->setLanguagePhrases([]);
        $state->setModuleConfig([]);
        $state->setGlobalConfig([]);
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
        $this->deleteDir(dirname($this->configDir));
    }

    public function testCmsLoadsAndCachesConfiguration(): void
    {
        file_put_contents($this->configDir . 'config.json', json_encode([
            'website_title' => 'DarkCore',
            'docker_timezone' => 'UTC',
        ], JSON_THROW_ON_ERROR));

        $loader = new ConfigProvider($this->configDir);

        $first = $loader->cms();
        file_put_contents($this->configDir . 'config.json', json_encode([
            'website_title' => 'Changed',
            'docker_timezone' => 'Europe/Kyiv',
        ], JSON_THROW_ON_ERROR));
        $second = $loader->cms();

        $this->assertSame('DarkCore', $first['website_title']);
        $this->assertSame($first, $second);
    }

    public function testModuleAndGlobalXmlConfigsLoadThroughLoader(): void
    {
        file_put_contents($this->configDir . 'email-templates.xml', '<config><smtp_host>mail.example.com</smtp_host></config>');
        file_put_contents($this->configDir . 'modules/login.xml', '<config><max_login_attempts>5</max_login_attempts></config>');

        $loader = new ConfigProvider($this->configDir);

        $this->assertSame('mail.example.com', $loader->globalXml('email-templates')['smtp_host'] ?? null);
        $this->assertSame('5', $loader->moduleConfig('login')['max_login_attempts'] ?? null);
    }

    public function testUsercpModuleAliasesResolveHyphenatedXmlFiles(): void
    {
        mkdir($this->configDir . 'modules/usercp/', 0777, true);
        file_put_contents($this->configDir . 'modules/usercp/my-account.xml', '<config><active>1</active></config>');
        file_put_contents($this->configDir . 'modules/usercp/add-stats.xml', '<config><max_stats>32767</max_stats></config>');
        file_put_contents($this->configDir . 'modules/usercp/buy-zen.xml', '<config><max_zen>2000000000</max_zen></config>');

        $loader = new ConfigProvider($this->configDir);

        $this->assertSame('1', $loader->moduleConfig('usercp.myaccount')['active'] ?? null);
        $this->assertSame('32767', $loader->moduleConfig('usercp.addstats')['max_stats'] ?? null);
        $this->assertSame('2000000000', $loader->moduleConfig('usercp.buyzen')['max_zen'] ?? null);
    }

    public function testTimezoneFallsBackToUtcWhenCmsIsMissing(): void
    {
        $loader = new ConfigProvider($this->configDir);

        $this->assertSame('UTC', $loader->timezone());
    }

    public function testTimezoneInitializerAppliesConfiguredTimezone(): void
    {
        file_put_contents($this->configDir . 'config.json', json_encode([
            'docker_timezone' => 'Europe/Kyiv',
        ], JSON_THROW_ON_ERROR));

        $loader = new ConfigProvider($this->configDir);
        $timezone = (new TimezoneInitializer($loader))->apply();

        $this->assertSame('Europe/Kyiv', $timezone);
        $this->assertSame('Europe/Kyiv', date_default_timezone_get());
    }

    private function deleteDir(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $target = $path . '/' . $item;
            if (is_dir($target)) {
                $this->deleteDir($target);
                continue;
            }

            @unlink($target);
        }

        @rmdir($path);
    }
}


