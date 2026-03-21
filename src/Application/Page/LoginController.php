<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Auth\AuthService;
use Darkheim\Infrastructure\View\ViewRenderer;

final class LoginController
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
                inline_message('error', lang('error_47', true));
                return;
            }

            if (isset($_POST['darkheimLogin_submit'])) {
                try {
                    (new AuthService())->login(
                        $_POST['darkheimLogin_user'] ?? '',
                        $_POST['darkheimLogin_pwd']  ?? ''
                    );
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $this->view->render('login', [
                'baseUrl'       => __BASE_URL__,
                'forgotPassUrl' => __BASE_URL__ . 'forgotpassword/',
                'registerUrl'   => __BASE_URL__ . 'register',
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}
