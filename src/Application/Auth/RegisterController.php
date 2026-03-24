<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

use Darkheim\Application\Account\Account;
use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Http\Redirector;
use Darkheim\Infrastructure\View\ViewRenderer;
use ReCaptcha\ReCaptcha;

final class RegisterController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (SessionManager::websiteAuthenticated()) {
            Redirector::go();
            return;
        }

        try {
            if (! BootstrapContext::moduleValue('active')) {
                MessageRenderer::inline('error', Translator::phrase('error_17'));
                return;
            }

            if (isset($_POST['darkheimRegister_submit'])) {
                try {
                    $account = new Account();
                    if (BootstrapContext::moduleValue('register_enable_recaptcha')) {
                        $recaptcha = new ReCaptcha(
                            BootstrapContext::moduleValue('register_recaptcha_secret_key'),
                        );
                        $resp = $recaptcha->verify($_POST['g-recaptcha-response'] ?? '', $_SERVER['REMOTE_ADDR']);
                        if (! $resp->isSuccess()) {
                            throw new \Exception(Translator::phrase('error_18'));
                        }
                    }
                    $account->registerAccount(
                        $_POST['darkheimRegister_user']  ?? '',
                        $_POST['darkheimRegister_pwd']   ?? '',
                        $_POST['darkheimRegister_pwdc']  ?? '',
                        $_POST['darkheimRegister_email'] ?? '',
                    );
                } catch (\Exception $ex) {
                    MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('register', [
                'baseUrl'          => __BASE_URL__,
                'loginUrl'         => __BASE_URL__ . 'login',
                'recaptchaEnabled' => (bool) BootstrapContext::moduleValue('register_enable_recaptcha'),
                'recaptchaSiteKey' => (string) BootstrapContext::moduleValue('register_recaptcha_site_key'),
                'userMinLen'       => (int) BootstrapContext::cmsValue('username_min_len', true),
                'userMaxLen'       => (int) BootstrapContext::cmsValue('username_max_len', true),
                'pwdMinLen'        => (int) BootstrapContext::cmsValue('password_min_len', true),
                'pwdMaxLen'        => (int) BootstrapContext::cmsValue('password_max_len', true),
                'tosUrl'           => __BASE_URL__ . 'tos',
            ]);
        } catch (\Exception $ex) {
            MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
