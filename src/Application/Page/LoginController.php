<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Auth\AuthService;
use Darkheim\Application\Language\Translator;
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
        if (\Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go();
            return;
        }

        try {
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_47'));
                return;
            }

            if (isset($_POST['darkheimLogin_submit'])) {
                try {
                    (new AuthService())->login(
                        $_POST['darkheimLogin_user'] ?? '',
                        $_POST['darkheimLogin_pwd']  ?? ''
                    );
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('login', [
                'baseUrl'       => __BASE_URL__,
                'forgotPassUrl' => __BASE_URL__ . 'forgotpassword/',
                'registerUrl'   => __BASE_URL__ . 'register',
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
