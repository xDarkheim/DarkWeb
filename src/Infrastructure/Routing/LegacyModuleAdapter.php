<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class LegacyModuleAdapter
{
    public function loadModule(string $page): bool
    {
        $moduleFile = __PATH_MODULES__ . $page . '.php';
        if (!is_file($moduleFile)) {
            return false;
        }

        @loadModuleConfigs($page);
        include $moduleFile;
        return true;
    }

    public function loadSubModule(string $page, string $subpage): bool
    {
        $path = $page . '/' . $subpage;
        $moduleFile = __PATH_MODULES__ . $path . '.php';
        if (!is_file($moduleFile)) {
            return false;
        }

        @loadModuleConfigs($page . '.' . $subpage);
        include $moduleFile;
        return true;
    }

    public function loadAdmincpModule(string $module): bool
    {
        $moduleFile = __PATH_ADMINCP_MODULES__ . $module . '.php';
        if (!is_file($moduleFile)) {
            return false;
        }

        include $moduleFile;
        return true;
    }
}

