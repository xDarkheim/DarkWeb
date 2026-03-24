<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Http\Redirector;
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
        if (SessionManager::websiteAuthenticated()) {
            Redirector::go();
            return;
        }

        try {
            if (! BootstrapContext::moduleValue('active')) {
                MessageRenderer::inline('error', Translator::phrase('error_47'));
                return;
            }

            if (isset($_POST['darkheimLogin_submit'])) {
                try {
                    new AuthService()->login(
                        $_POST['darkheimLogin_user'] ?? '',
                        $_POST['darkheimLogin_pwd']  ?? '',
                    );
                } catch (\Exception $ex) {
                    MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('login', [
                'baseUrl'       => __BASE_URL__,
                'forgotPassUrl' => __BASE_URL__ . 'forgotpassword/',
                'registerUrl'   => __BASE_URL__ . 'register',
            ]);
        } catch (\Exception $ex) {
            MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
