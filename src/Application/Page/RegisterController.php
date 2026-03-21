<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Account\Account;
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
        if (isLoggedIn()) {
            redirect();
            return;
        }

        try {
            if (!mconfig('active')) {
                inline_message('error', lang('error_17', true));
                return;
            }

            if (isset($_POST['darkheimRegister_submit'])) {
                try {
                    $account = new Account();
                    if (mconfig('register_enable_recaptcha')) {
                        $recaptcha = new \ReCaptcha\ReCaptcha(mconfig('register_recaptcha_secret_key'));
                        $resp = $recaptcha->verify($_POST['g-recaptcha-response'] ?? '', $_SERVER['REMOTE_ADDR']);
                        if (!$resp->isSuccess()) throw new \Exception(lang('error_18', true));
                    }
                    $account->registerAccount(
                        $_POST['darkheimRegister_user']  ?? '',
                        $_POST['darkheimRegister_pwd']   ?? '',
                        $_POST['darkheimRegister_pwdc']  ?? '',
                        $_POST['darkheimRegister_email'] ?? ''
                    );
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $this->view->render('register', [
                'baseUrl'           => __BASE_URL__,
                'loginUrl'          => __BASE_URL__ . 'login',
                'recaptchaEnabled'  => (bool) mconfig('register_enable_recaptcha'),
                'recaptchaSiteKey'  => (string) mconfig('register_recaptcha_site_key'),
                'userMinLen'        => (int) config('username_min_len', true),
                'userMaxLen'        => (int) config('username_max_len', true),
                'pwdMinLen'         => (int) config('password_min_len', true),
                'pwdMaxLen'         => (int) config('password_max_len', true),
                'tosUrl'            => __BASE_URL__ . 'tos',
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}
