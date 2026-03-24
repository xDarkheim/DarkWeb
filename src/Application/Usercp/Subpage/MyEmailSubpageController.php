<?php

declare(strict_types=1);

namespace Darkheim\Application\Usercp\Subpage;

use Darkheim\Application\Account\Account;
use Darkheim\Application\Shared\Language\Translator;
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
        if (! \Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go(1, 'login');
            return;
        }

        try {
            if (! \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                throw new \Exception(Translator::phrase('error_47'));
            }

            if (isset($_POST['darkheimEmail_submit'])) {
                try {
                    $account = new Account();
                    $account->changeEmailAddress(
                        $_SESSION['userid'],
                        (string) ($_POST['darkheimEmail_newemail'] ?? ''),
                        (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                    );
                    \Darkheim\Application\Shared\UI\MessageRenderer::toast('success', \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('require_verification') ? Translator::phrase('success_19') : Translator::phrase('success_20'));
                } catch (\Exception $ex) {
                    \Darkheim\Application\Shared\UI\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('subpages/usercp/myemail', [
                'pageTitle'   => Translator::phrase('module_titles_txt_5'),
                'cardTitle'   => Translator::phrase('module_titles_txt_5'),
                'submitLabel' => Translator::phrase('changemail_txt_1'),
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\Shared\UI\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
