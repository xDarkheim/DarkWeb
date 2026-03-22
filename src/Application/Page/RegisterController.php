<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Account\Account;
use Darkheim\Application\Language\Translator;
use Darkheim\Infrastructure\View\ViewRenderer;

final class RegisterController
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
                \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_17'));
                return;
            }

            if (isset($_POST['darkheimRegister_submit'])) {
                try {
                    $account = new Account();
                    if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('register_enable_recaptcha')) {
                        $recaptcha = new \ReCaptcha\ReCaptcha(\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('register_recaptcha_secret_key'));
                        $resp = $recaptcha->verify($_POST['g-recaptcha-response'] ?? '', $_SERVER['REMOTE_ADDR']);
                        if (!$resp->isSuccess()) throw new \Exception(Translator::phrase('error_18'));
                    }
                    $account->registerAccount(
                        $_POST['darkheimRegister_user']  ?? '',
                        $_POST['darkheimRegister_pwd']   ?? '',
                        $_POST['darkheimRegister_pwdc']  ?? '',
                        $_POST['darkheimRegister_email'] ?? ''
                    );
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('register', [
                'baseUrl'           => __BASE_URL__,
                'loginUrl'          => __BASE_URL__ . 'login',
                'recaptchaEnabled'  => (bool) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('register_enable_recaptcha'),
                'recaptchaSiteKey'  => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('register_recaptcha_site_key'),
                'userMinLen'        => (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('username_min_len', true),
                'userMaxLen'        => (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('username_max_len', true),
                'pwdMinLen'         => (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('password_min_len', true),
                'pwdMaxLen'         => (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('password_max_len', true),
                'tosUrl'            => __BASE_URL__ . 'tos',
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
