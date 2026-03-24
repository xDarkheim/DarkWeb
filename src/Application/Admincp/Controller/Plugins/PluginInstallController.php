<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\Plugins;

use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Plugins\Plugins;
use Darkheim\Infrastructure\View\ViewRenderer;

final class PluginInstallController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $systemEnabled = (bool) BootstrapContext::cmsValue('plugins_system_enable', true);

        if (isset($_POST['submit'])) {
            if ($_FILES['file']['error'] > 0) {
                MessageRenderer::toast('error', 'There has been an error uploading the file.');
            } else {
                $plugin = new Plugins();
                $plugin->importPlugin($_FILES);
            }
        }

        $this->view->render('admincp/plugininstall', [
            'systemEnabled' => $systemEnabled,
        ]);
    }
}
