<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Language\Translator;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Email\Email;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ContactController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_47'));
                return;
            }

            if (isset($_POST['submit'])) {
                try {
                    $emailVal = $_POST['contact_email']   ?? '';
                    $msgVal   = $_POST['contact_message'] ?? '';
                    if (!Validator::Email($emailVal))           throw new \Exception(Translator::phrase('error_9'));
                    if (!Validator::Length($msgVal, 300, 10))   throw new \Exception(Translator::phrase('error_57'));

                    $emailConfigs = BootstrapContext::configProvider()?->globalXml('email-templates');
                    if (!is_array($emailConfigs)) throw new \Exception(Translator::phrase('error_21'));

                    $mail = new Email();
                    $mail->_subject = \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('subject');
                    $mail->setFrom($emailConfigs['send_from'], $emailConfigs['send_name'] . ' - Contact Form');
                    $mail->setReplyTo($emailVal);
                    $mail->_message = $msgVal;
                    $mail->addAddress(\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('sendto'));
                    $mail->send();

                    \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_22'));
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('contact');
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
