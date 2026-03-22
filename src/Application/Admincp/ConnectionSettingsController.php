<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Config\ConfigRepository;
use Darkheim\Infrastructure\Database\dB;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ConnectionSettingsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $allowedSettings = ['settings_submit', 'SQL_DB_HOST', 'SQL_DB_NAME', 'SQL_DB_USER', 'SQL_DB_PASS', 'SQL_DB_PORT', 'SQL_PASSWORD_ENCRYPTION'];

        if (isset($_POST['settings_submit'])) {
            try {
                if (! isset($_POST['SQL_DB_HOST'])) {
                    throw new \RuntimeException('Invalid Host setting.');
                }
                if (! isset($_POST['SQL_DB_NAME'])) {
                    throw new \RuntimeException('Invalid Database setting.');
                }
                if (! isset($_POST['SQL_DB_USER'])) {
                    throw new \RuntimeException('Invalid User setting.');
                }
                if (! isset($_POST['SQL_DB_PASS'])) {
                    throw new \RuntimeException('Invalid Password setting.');
                }
                if (! isset($_POST['SQL_DB_PORT']) || ! Validator::UnsignedNumber($_POST['SQL_DB_PORT'])) {
                    throw new \RuntimeException('Invalid Port setting.');
                }
                if (! isset($_POST['SQL_PASSWORD_ENCRYPTION']) || ! in_array($_POST['SQL_PASSWORD_ENCRYPTION'], ['none', 'wzmd5', 'phpmd5', 'sha256'])) {
                    throw new \RuntimeException('Invalid password encryption setting.');
                }
                $setting = [
                    'SQL_DB_HOST'             => $_POST['SQL_DB_HOST'],
                    'SQL_DB_NAME'             => $_POST['SQL_DB_NAME'],
                    'SQL_DB_USER'             => $_POST['SQL_DB_USER'],
                    'SQL_DB_PASS'             => $_POST['SQL_DB_PASS'],
                    'SQL_DB_PORT'             => $_POST['SQL_DB_PORT'],
                    'SQL_PASSWORD_ENCRYPTION' => $_POST['SQL_PASSWORD_ENCRYPTION'],
                ];

                $testDb = new dB($setting['SQL_DB_HOST'], $setting['SQL_DB_PORT'], $setting['SQL_DB_NAME'], $setting['SQL_DB_USER'], $setting['SQL_DB_PASS']);
                if ($testDb->dead) {
                    throw new \RuntimeException('The connection to database was unsuccessful, settings not saved.');
                }

                $cmsConfigurations = BootstrapContext::configProvider()?->cms() ?? [];
                foreach (array_keys($setting) as $k) {
                    if (! in_array($k, $allowedSettings, true)) {
                        throw new \RuntimeException('One or more submitted setting is not editable.');
                    }
                    $cmsConfigurations[$k] = $setting[$k];
                }

                new ConfigRepository(__PATH_CONFIGS__)->saveCms($cmsConfigurations);
                \Darkheim\Application\View\MessageRenderer::toast('success', 'Settings successfully saved!');
            } catch (\Exception $ex) {
                \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
            }
        }

        $this->view->render('admincp/connectionsettings', [
            'host'       => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('SQL_DB_HOST', true),
            'database'   => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('SQL_DB_NAME', true),
            'user'       => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('SQL_DB_USER', true),
            'password'   => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('SQL_DB_PASS', true),
            'port'       => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('SQL_DB_PORT', true),
            'encryption' => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('SQL_PASSWORD_ENCRYPTION', true),
        ]);
    }
}
