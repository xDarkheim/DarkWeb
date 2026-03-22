<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\Plugins\Plugins;
use Darkheim\Infrastructure\View\ViewRenderer;

final class PluginsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        define('PLUGIN_ALLOW_UNINSTALL', true);
        $plugins = new Plugins();

        if (isset($_REQUEST['enable'])) {
            $plugins->updatePluginStatus($_REQUEST['enable'], 1);
        }
        if (isset($_REQUEST['disable'])) {
            $plugins->updatePluginStatus($_REQUEST['disable'], 0);
        }
        if (isset($_REQUEST['uninstall'])) {
            if ($plugins->uninstallPlugin($_REQUEST['uninstall'])) {
                message('success', 'Plugin uninstalled.');
            } else {
                message('error', 'Could not uninstall plugin.');
            }
            if (!$plugins->rebuildPluginsCache()) {
                message('error', 'Could not update plugins cache.');
            }
        }

        $pluginList  = $plugins->retrieveInstalledPlugins();
        $rows        = [];
        if (is_array($pluginList)) {
            foreach ($pluginList as $p) {
                $isOn    = (int) ($p['status'] ?? 0) === 1;
                $rows[]  = [
                    'id'            => (string) ($p['id'] ?? ''),
                    'name'          => (string) ($p['name'] ?? ''),
                    'author'        => (string) ($p['author'] ?? ''),
                    'version'       => (string) ($p['version'] ?? ''),
                    'compatibility' => implode(', ', explode('|', (string) ($p['compatibility'] ?? ''))),
                    'installDate'   => date('Y-m-d', (int) ($p['install_date'] ?? 0)),
                    'isEnabled'     => $isOn,
                    'enableUrl'     => admincp_base('plugins&enable=' . ($p['id'] ?? '')),
                    'disableUrl'    => admincp_base('plugins&disable=' . ($p['id'] ?? '')),
                    'uninstallUrl'  => admincp_base('plugins&uninstall=' . ($p['id'] ?? '')),
                    'allowUninstall'=> PLUGIN_ALLOW_UNINSTALL,
                ];
            }
        }

        $this->view->render('admincp/plugins', [
            'systemEnabled' => (bool) config('plugins_system_enable', true),
            'importUrl'     => admincp_base('plugin_install'),
            'rows'          => $rows,
        ]);
    }
}
