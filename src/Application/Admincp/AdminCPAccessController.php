<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\View\ViewRenderer;

final class AdminCPAccessController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (isset($_POST['settings_submit'])) {
            try {
                $cmsConfigurations = cmsConfigs();
                $newAdminUser      = $_POST['new_admin'];
                $newAdminLevel     = $_POST['new_access'];
                unset($_POST['settings_submit'], $_POST['new_admin'], $_POST['new_access']);

                $adminAccounts = [];
                foreach ($_POST as $adminUsername => $accessLevel) {
                    if (!Validator::AlphaNumeric($adminUsername) || !Validator::UsernameLength($adminUsername)) {
                        throw new \RuntimeException('Invalid username.');
                    }
                    if (!array_key_exists($adminUsername, config('admins', true))) {
                        continue;
                    }
                    if (!Validator::UnsignedNumber($accessLevel) || !Validator::Number($accessLevel, 100)) {
                        throw new \RuntimeException('Access level must be 0–100.');
                    }
                    if ($accessLevel == 0) {
                        if ($adminUsername === $_SESSION['username']) {
                            throw new \RuntimeException('You cannot remove yourself.');
                        }
                        continue;
                    }
                    $adminAccounts[$adminUsername] = (int) $accessLevel;
                }

                if (check_value($newAdminUser)) {
                    if (array_key_exists($newAdminUser, config('admins', true))) {
                        throw new \RuntimeException('Admin already exists.');
                    }
                    if (!Validator::UnsignedNumber($newAdminLevel)) {
                        throw new \RuntimeException('Access level must be 1–100.');
                    }
                    $adminAccounts[$newAdminUser] = (int) $newAdminLevel;
                }

                $cmsConfigurations['admins'] = $adminAccounts;
                $cfgFile = fopen(__PATH_CONFIGS__ . 'config.json', 'wb');
                if (!$cfgFile) {
                    throw new \RuntimeException('Could not open configuration file.');
                }
                fwrite($cfgFile, json_encode($cmsConfigurations, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
                fclose($cfgFile);
                message('success', 'Settings saved!');
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        $admins    = config('admins', true);
        $adminRows = [];
        if (is_array($admins)) {
            foreach ($admins as $adminName => $level) {
                $adminRows[] = [
                    'username' => (string) $adminName,
                    'level'    => (string) $level,
                ];
            }
        }

        $this->view->render('admincp/admincpaccess', [
            'adminRows' => $adminRows,
            'hasAdmins' => $adminRows !== [],
        ]);
    }
}

