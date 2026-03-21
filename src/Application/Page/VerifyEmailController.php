<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Account\Account;
use Darkheim\Infrastructure\View\ViewRenderer;

final class VerifyEmailController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (!isset($_GET['op'])) {
            redirect();
            return;
        }

        $result  = null; // ['type' => 'success'|'error', 'message' => string]
        $account = new Account();

        try {
            switch ((int) $_GET['op']) {
                case 1: // Password change request
                    if (!isset($_GET['uid'], $_GET['ac'])) { redirect(); return; }
                    $account->changePasswordVerificationProcess($_GET['uid'], $_GET['ac']);
                    break;

                case 2: // Registration email verification
                    if (!isset($_GET['user'], $_GET['key'])) { redirect(); return; }
                    $account->verifyRegistrationProcess($_GET['user'], $_GET['key']);
                    break;

                default: // Email change
                    if (!isset($_GET['uid'], $_GET['email'], $_GET['key'])) { redirect(); return; }
                    $account->changeEmailVerificationProcess($_GET['uid'], $_GET['email'], $_GET['key']);
                    $result = ['type' => 'success', 'message' => lang('success_20', true)];
            }
        } catch (\Exception $ex) {
            $result = ['type' => 'error', 'message' => $ex->getMessage()];
        }

        $resultHtml = '';
        if (is_array($result)) {
            ob_start();
            inline_message((string) $result['type'], (string) $result['message']);
            $resultHtml = (string) ob_get_clean();
        }

        $this->view->render('verifyemail', [
            'pageTitle'  => lang('module_titles_txt_20', true),
            'resultHtml' => $resultHtml,
        ]);
    }
}
