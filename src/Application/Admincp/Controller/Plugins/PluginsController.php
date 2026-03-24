<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\Plugins;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
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
        $admincpUrl = new AdmincpUrlGenerator();
        $plugins    = new Plugins();

        if (isset($_REQUEST['enable'])) {
            $plugins->updatePluginStatus($_REQUEST['enable'], 1);
        }
        if (isset($_REQUEST['disable'])) {
            $plugins->updatePluginStatus($_REQUEST['disable'], 0);
        }
        if (isset($_REQUEST['uninstall'])) {
            if ($plugins->uninstallPlugin($_REQUEST['uninstall'])) {
                MessageRenderer::toast('success', 'Plugin uninstalled.');
            } else {
                MessageRenderer::toast('error', 'Could not uninstall plugin.');
            }
            if (! $plugins->rebuildPluginsCache()) {
                MessageRenderer::toast('error', 'Could not update plugins cache.');
            }
        }

        $pluginList = $plugins->retrieveInstalledPlugins();
        $rows       = [];
        if (is_array($pluginList)) {
            foreach ($pluginList as $p) {
                $isOn   = (int) ($p['status'] ?? 0) === 1;
                $rows[] = [
                    'id'             => (string) ($p['id'] ?? ''),
                    'name'           => (string) ($p['name'] ?? ''),
                    'author'         => (string) ($p['author'] ?? ''),
                    'version'        => (string) ($p['version'] ?? ''),
                    'compatibility'  => implode(', ', explode('|', (string) ($p['compatibility'] ?? ''))),
                    'installDate'    => date('Y-m-d', (int) ($p['install_date'] ?? 0)),
                    'isEnabled'      => $isOn,
                    'enableUrl'      => $admincpUrl->base('plugins&enable=' . ($p['id'] ?? '')),
                    'disableUrl'     => $admincpUrl->base('plugins&disable=' . ($p['id'] ?? '')),
                    'uninstallUrl'   => $admincpUrl->base('plugins&uninstall=' . ($p['id'] ?? '')),
                    'allowUninstall' => PLUGIN_ALLOW_UNINSTALL,
                ];
            }
        }

        $this->view->render('admincp/plugins', [
            'systemEnabled' => (bool) BootstrapContext::cmsValue('plugins_system_enable', true),
            'importUrl'     => $admincpUrl->base('plugin_install'),
            'rows'          => $rows,
        ]);
    }
}
