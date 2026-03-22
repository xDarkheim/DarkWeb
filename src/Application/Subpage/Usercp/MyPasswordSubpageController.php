<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Account\Account;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Language\Translator;
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
        if (! \Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go(1, 'login');
            return;
        }

        try {
            if (! \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                throw new \Exception(Translator::phrase('error_47'));
            }

            $common = new Common();
            if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('change_password_email_verification') && $common->hasActivePasswordChangeRequest($_SESSION['userid'])) {
                throw new \Exception(Translator::phrase('error_19'));
            }

            if (isset($_POST['darkheimPassword_submit'])) {
                try {
                    $account = new Account();
                    if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('change_password_email_verification')) {
                        $account->changePasswordProcess_verifyEmail(
                            $_SESSION['userid'],
                            $_SESSION['username'],
                            (string) ($_POST['darkheimPassword_current'] ?? ''),
                            (string) ($_POST['darkheimPassword_new'] ?? ''),
                            (string) ($_POST['darkheimPassword_newconfirm'] ?? ''),
                            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                        );
                    } else {
                        $account->changePasswordProcess(
                            $_SESSION['userid'],
                            $_SESSION['username'],
                            (string) ($_POST['darkheimPassword_current'] ?? ''),
                            (string) ($_POST['darkheimPassword_new'] ?? ''),
                            (string) ($_POST['darkheimPassword_newconfirm'] ?? ''),
                        );
                    }
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('subpages/usercp/mypassword', [
                'pageTitle'    => Translator::phrase('module_titles_txt_6'),
                'cardTitle'    => Translator::phrase('module_titles_txt_6'),
                'currentLabel' => Translator::phrase('changepassword_txt_1'),
                'newLabel'     => Translator::phrase('changepassword_txt_2'),
                'confirmLabel' => Translator::phrase('changepassword_txt_3'),
                'submitLabel'  => Translator::phrase('changepassword_txt_4'),
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
