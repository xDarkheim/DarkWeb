<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Account\Account;
use Darkheim\Infrastructure\View\ViewRenderer;

final class MyEmailSubpageController
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

            if (isset($_POST['darkheimEmail_submit'])) {
                try {
                    $account = new Account();
                    $account->changeEmailAddress(
                        $_SESSION['userid'],
                        (string) ($_POST['darkheimEmail_newemail'] ?? ''),
                        (string) ($_SERVER['REMOTE_ADDR'] ?? '')
                    );
                    message('success', mconfig('require_verification') ? lang('success_19', true) : lang('success_20', true));
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $this->view->render('subpages/usercp/myemail', [
                'pageTitle'   => lang('module_titles_txt_5', true),
                'cardTitle'   => lang('module_titles_txt_5', true),
                'submitLabel' => lang('changemail_txt_1', true),
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}

