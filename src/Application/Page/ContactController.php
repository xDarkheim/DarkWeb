<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Domain\Validator;
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
            if (!mconfig('active')) {
                inline_message('error', lang('error_47', true));
                return;
            }

            if (isset($_POST['submit'])) {
                try {
                    $emailVal = $_POST['contact_email']   ?? '';
                    $msgVal   = $_POST['contact_message'] ?? '';
                    if (!Validator::Email($emailVal))           throw new \Exception(lang('error_9',  true));
                    if (!Validator::Length($msgVal, 300, 10))   throw new \Exception(lang('error_57', true));

                    $emailConfigs = gconfig('email-templates', true);
                    if (!is_array($emailConfigs)) throw new \Exception(lang('error_21', true));

                    $mail = new Email();
                    $mail->_subject = mconfig('subject');
                    $mail->setFrom($emailConfigs['send_from'], $emailConfigs['send_name'] . ' - Contact Form');
                    $mail->setReplyTo($emailVal);
                    $mail->_message = $msgVal;
                    $mail->addAddress(mconfig('sendto'));
                    $mail->send();

                    message('success', lang('success_22', true));
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $this->view->render('contact');
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}
