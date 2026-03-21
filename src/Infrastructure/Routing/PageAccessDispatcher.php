<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class PageAccessDispatcher
{
    /**
     * @param array<string, mixed> $context
     */
    public function dispatch(string $access, string $theme, array $context = []): void
    {
        switch ($access) {
            case 'index':
                if (!$this->themeExists($theme)) {
                    throw new \Exception('The chosen theme cannot be loaded (' . $theme . ').');
                }
                if ($context !== []) {
                    extract($context, EXTR_SKIP);
                }
                include __PATH_THEMES__ . $theme . '/index.php';
                return;
            case 'api':
            case 'cron':
            case 'admincp':
            case 'install':
                return;
            default:
                throw new \Exception('Access forbidden.');
        }
    }

    private function themeExists(string $theme): bool
    {
        return is_file(__PATH_THEMES__ . $theme . '/index.php');
    }
}

