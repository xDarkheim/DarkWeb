<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class AdmincpModuleDispatcher
{
    /**
     * @param array<string, mixed> $context Variables to extract into the module's scope.
     */
    public function dispatch(string $module, array $context = []): bool
    {
        $moduleFile = __PATH_ADMINCP_MODULES__ . $module . '.php';
        if (!is_file($moduleFile)) {
            return false;
        }

        if ($context !== []) {
            extract($context, EXTR_SKIP);
        }

        include $moduleFile;
        return true;
    }
}

