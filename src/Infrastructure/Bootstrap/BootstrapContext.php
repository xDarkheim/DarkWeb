<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Bootstrap;

use Darkheim\Infrastructure\Routing\Handler;

final class BootstrapContext
{
    private static ?ConfigProvider $configProvider = null;
    private static ?RuntimeState $runtimeState = null;
    private static ?Handler $handler = null;

    public static function initialize(ConfigProvider $configProvider, RuntimeState $runtimeState, Handler $handler): void
    {
        self::$configProvider = $configProvider;
        self::$runtimeState = $runtimeState;
        self::$handler = $handler;
    }

    public static function configProvider(): ?ConfigProvider
    {
        return self::$configProvider;
    }

    public static function runtimeState(): ?RuntimeState
    {
        return self::$runtimeState;
    }

    public static function handler(): ?Handler
    {
        return self::$handler;
    }

    public static function cmsValue(string $key, mixed $default = null): mixed
    {
        $config = [];
        try {
            $config = self::$configProvider?->cms() ?? [];
        } catch (\Throwable) {
            $config = [];
        }

        if (array_key_exists($key, $config)) {
            return $config[$key];
        }

        if (isset($GLOBALS['_TEST_CMS_CONFIG']) && is_array($GLOBALS['_TEST_CMS_CONFIG']) && array_key_exists($key, $GLOBALS['_TEST_CMS_CONFIG'])) {
            return $GLOBALS['_TEST_CMS_CONFIG'][$key];
        }

        return $default;
    }

    public static function moduleValue(string $key, mixed $default = null): mixed
    {
        $config = self::$runtimeState?->moduleConfig() ?? [];
        return array_key_exists($key, $config) ? $config[$key] : $default;
    }

    public static function loadModuleConfig(string $module): void
    {
        if ($module === '') {
            self::$runtimeState?->setModuleConfig([]);
            return;
        }

        $result = self::$configProvider?->moduleConfig($module);
        self::$runtimeState?->setModuleConfig(is_array($result) ? $result : []);
    }
}

