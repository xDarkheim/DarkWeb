<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Account\Account;
use Darkheim\Application\Auth\Common;
use Darkheim\Infrastructure\View\ViewRenderer;

final class MyPasswordSubpageController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (!isLoggedIn()) {
            redirect(1, 'login');
            return;
        }

        try {
            if (!mconfig('active')) {
                throw new \Exception(lang('error_47', true));
            }

            $common = new Common();
            if (mconfig('change_password_email_verification') && $common->hasActivePasswordChangeRequest($_SESSION['userid'])) {
                throw new \Exception(lang('error_19', true));
            }

            if (isset($_POST['darkheimPassword_submit'])) {
                try {
                    $account = new Account();
                    if (mconfig('change_password_email_verification')) {
                        $account->changePasswordProcess_verifyEmail(
                            $_SESSION['userid'],
                            $_SESSION['username'],
                            (string) ($_POST['darkheimPassword_current'] ?? ''),
                            (string) ($_POST['darkheimPassword_new'] ?? ''),
                            (string) ($_POST['darkheimPassword_newconfirm'] ?? ''),
                            (string) ($_SERVER['REMOTE_ADDR'] ?? '')
                        );
                    } else {
                        $account->changePasswordProcess(
                            $_SESSION['userid'],
                            $_SESSION['username'],
                            (string) ($_POST['darkheimPassword_current'] ?? ''),
                            (string) ($_POST['darkheimPassword_new'] ?? ''),
                            (string) ($_POST['darkheimPassword_newconfirm'] ?? '')
                        );
                    }
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $this->view->render('subpages/usercp/mypassword', [
                'pageTitle'          => lang('module_titles_txt_6', true),
                'cardTitle'          => lang('module_titles_txt_6', true),
                'currentLabel'       => lang('changepassword_txt_1', true),
                'newLabel'           => lang('changepassword_txt_2', true),
                'confirmLabel'       => lang('changepassword_txt_3', true),
                'submitLabel'        => lang('changepassword_txt_4', true),
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}

