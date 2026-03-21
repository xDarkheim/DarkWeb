<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class HomeController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $database = Connection::Database('MuOnline');

        if ($this->shouldWarnAboutInstallDirectory()) {
            message('warning', 'public/install/ directory still exists — rename or delete it.', 'WARNING');
        }

        $this->view->render('admincp/home', [
            'showInstallWarning' => $this->shouldWarnAboutInstallDirectory(),
            'installWarningHtml' => $this->installWarningHtml(),
            'statCards' => [
                [
                    'iconClass' => 'bi bi-people-fill',
                    'iconStyle' => 'color:#4caf50;',
                    'backgroundStyle' => 'background:#1a2a1a;',
                    'value' => $this->countResult($database, 'SELECT COUNT(*) as result FROM MEMB_INFO'),
                    'label' => 'Registered Accounts',
                ],
                [
                    'iconClass' => 'bi bi-person-fill-slash',
                    'iconStyle' => 'color:#ef5350;',
                    'backgroundStyle' => 'background:#2a1a1a;',
                    'value' => $this->countResult($database, 'SELECT COUNT(*) as result FROM MEMB_INFO WHERE bloc_code = 1'),
                    'label' => 'Banned Accounts',
                ],
                [
                    'iconClass' => 'bi bi-controller',
                    'iconStyle' => 'color:#42a5f5;',
                    'backgroundStyle' => 'background:#1a1a2a;',
                    'value' => $this->countResult($database, 'SELECT COUNT(*) as result FROM Character'),
                    'label' => 'Characters',
                ],
                [
                    'iconClass' => 'bi bi-list-task',
                    'iconStyle' => 'color:#c8a96e;',
                    'backgroundStyle' => 'background:#1a2200;',
                    'value' => $this->countResult($database, 'SELECT COUNT(*) as result FROM ' . Cron),
                    'label' => 'Cron Tasks',
                ],
            ],
            'systemRows' => [
                ['label' => 'Operating System', 'value' => PHP_OS, 'valueClass' => ''],
                ['label' => 'PHP Version', 'value' => PHP_VERSION, 'valueClass' => ''],
                ['label' => 'CMS Version', 'value' => __CMS_VERSION__, 'valueClass' => ''],
                ['label' => 'Server Time', 'value' => date('Y-m-d H:i'), 'valueClass' => ''],
                [
                    'label' => 'Plugin System',
                    'value' => (bool) config('plugins_system_enable', true) ? 'Enabled' : 'Disabled',
                    'valueClass' => (bool) config('plugins_system_enable', true) ? 'badge-status on' : 'badge-status off',
                ],
            ],
            'quickActions' => [
                ['url' => admincp_base('addnews'), 'iconClass' => 'bi bi-newspaper', 'label' => 'Publish News'],
                ['url' => admincp_base('searchaccount'), 'iconClass' => 'bi bi-search', 'label' => 'Search Account'],
                ['url' => admincp_base('banaccount'), 'iconClass' => 'bi bi-slash-circle', 'label' => 'Ban Account'],
                ['url' => admincp_base('creditsmanager'), 'iconClass' => 'bi bi-cash-coin', 'label' => 'Credits Manager'],
                ['url' => admincp_base('cachemanager'), 'iconClass' => 'bi bi-arrow-clockwise', 'label' => 'Clear Cache'],
                ['url' => admincp_base('website_settings'), 'iconClass' => 'bi bi-gear', 'label' => 'Settings'],
            ],
            'admins' => $this->adminRows(),
        ]);
    }

    private function shouldWarnAboutInstallDirectory(): bool
    {
        $installDir = __PUBLIC_DIR__ . 'install/';
        $installHtaccess = $installDir . '.htaccess';

        if (!file_exists($installDir)) {
            return false;
        }

        if (!file_exists($installHtaccess)) {
            return true;
        }

        $contents = @file_get_contents($installHtaccess);
        if (!is_string($contents)) {
            return true;
        }

        return strpos($contents, 'Require all denied') === false;
    }

    private function installWarningHtml(): string
    {
        ob_start();
        inline_message('warning', 'Your public/install/ directory still exists. It is strongly recommended that you rename or delete it before going live.');
        return (string) ob_get_clean();
    }

    private function countResult(object $database, string $query): string
    {
        try {
            $row = $database->query_fetch_single($query);
            if (!is_array($row) || !isset($row['result']) || !is_numeric($row['result'])) {
                return '?';
            }

            return number_format((float) $row['result']);
        } catch (\Exception) {
            return '?';
        }
    }

    /**
     * @return array<int, array{name:string, level:string}>
     */
    private function adminRows(): array
    {
        $admins = config('admins', true);
        if (!is_array($admins)) {
            return [];
        }

        $rows = [];
        foreach ($admins as $adminName => $adminLevel) {
            $rows[] = [
                'name' => (string) $adminName,
                'level' => 'Level ' . (string) $adminLevel,
            ];
        }

        return $rows;
    }
}

