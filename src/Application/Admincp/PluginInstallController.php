<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

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
        $systemEnabled = (bool) config('plugins_system_enable', true);

        if (isset($_POST['submit'])) {
            if ($_FILES['file']['error'] > 0) {
                message('error', 'There has been an error uploading the file.');
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

