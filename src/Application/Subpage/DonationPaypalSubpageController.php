<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage;

use Darkheim\Application\Language\Translator;
use Darkheim\Infrastructure\View\ViewRenderer;

final class DonationPaypalSubpageController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (!\Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go(1, 'login');
            return;
        }

        if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
            \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_47'));
            return;
        }

        $formAction = \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_enable_sandbox')
            ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
            : 'https://www.paypal.com/cgi-bin/webscr';

        $this->view->render('subpages/donation/paypal', [
            'pageTitle'       => Translator::phrase('module_titles_txt_21'),
            'conversionRate'  => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_conversion_rate'),
            'formAction'      => $formAction,
            'orderId'         => md5((string) time()),
            'paypalEmail'     => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_email'),
            'paypalTitle'     => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_title'),
            'paypalCurrency'  => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_currency'),
            'donationText'    => Translator::phrase('donation_txt_2'),
            'returnUrl'       => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_return_url'),
            'notifyUrl'       => (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('paypal_notify_url'),
            'customUserId'    => (string) ($_SESSION['userid'] ?? ''),
        ]);
    }
}

