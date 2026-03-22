<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Account\Account;
use Darkheim\Application\Language\Translator;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ForgotPasswordController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (\Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go();
            return;
        }

        try {
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_47'));
                return;
            }

            // Email verification link (sent by email): /verifyemail/?op=0&ui=...&ue=...&key=...
            if (isset($_GET['ui'], $_GET['ue'], $_GET['key'])) {
                try {
                    (new Account())->passwordRecoveryVerificationProcess(
                        $_GET['ui'],
                        $_GET['ue'],
                        $_GET['key']
                    );
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
                }
                return;
            }

            // Form submission
            if (isset($_POST['darkheimEmail_submit'])) {
                try {
                    (new Account())->passwordRecoveryProcess(
                        $_POST['darkheimEmail_current'] ?? '',
                        $_SERVER['REMOTE_ADDR']
                    );
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('forgotpassword', [
                'baseUrl'  => __BASE_URL__,
                'loginUrl' => __BASE_URL__ . 'login',
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
