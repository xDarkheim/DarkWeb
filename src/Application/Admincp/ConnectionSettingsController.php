<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Domain\Validator;
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
                if (!isset($_POST['SQL_DB_HOST'])) throw new \RuntimeException('Invalid Host setting.');
                if (!isset($_POST['SQL_DB_NAME'])) throw new \RuntimeException('Invalid Database setting.');
                if (!isset($_POST['SQL_DB_USER'])) throw new \RuntimeException('Invalid User setting.');
                if (!isset($_POST['SQL_DB_PASS'])) throw new \RuntimeException('Invalid Password setting.');
                if (!isset($_POST['SQL_DB_PORT']) || !Validator::UnsignedNumber($_POST['SQL_DB_PORT'])) throw new \RuntimeException('Invalid Port setting.');
                if (!isset($_POST['SQL_PASSWORD_ENCRYPTION']) || !in_array($_POST['SQL_PASSWORD_ENCRYPTION'], ['none', 'wzmd5', 'phpmd5', 'sha256'])) {
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

                $cmsConfigurations = cmsConfigs();
                foreach (array_keys($setting) as $k) {
                    if (!in_array($k, $allowedSettings, true)) {
                        throw new \RuntimeException('One or more submitted setting is not editable.');
                    }
                    $cmsConfigurations[$k] = $setting[$k];
                }

                $cfgFile = fopen(__PATH_CONFIGS__ . 'config.json', 'wb');
                if (!$cfgFile) throw new \RuntimeException('There was a problem opening the configuration file.');
                fwrite($cfgFile, json_encode($cmsConfigurations, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
                fclose($cfgFile);
                message('success', 'Settings successfully saved!');
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        $this->view->render('admincp/connectionsettings', [
            'host'       => (string) config('SQL_DB_HOST', true),
            'database'   => (string) config('SQL_DB_NAME', true),
            'user'       => (string) config('SQL_DB_USER', true),
            'password'   => (string) config('SQL_DB_PASS', true),
            'port'       => (string) config('SQL_DB_PORT', true),
            'encryption' => (string) config('SQL_PASSWORD_ENCRYPTION', true),
        ]);
    }
}

