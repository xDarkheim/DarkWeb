<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage;

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
        if (!isLoggedIn()) {
            redirect(1, 'login');
            return;
        }

        if (!mconfig('active')) {
            inline_message('error', lang('error_47'));
            return;
        }

        $formAction = mconfig('paypal_enable_sandbox')
            ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
            : 'https://www.paypal.com/cgi-bin/webscr';

        $this->view->render('subpages/donation/paypal', [
            'pageTitle'       => lang('module_titles_txt_21'),
            'conversionRate'  => (string) mconfig('paypal_conversion_rate'),
            'formAction'      => $formAction,
            'orderId'         => md5((string) time()),
            'paypalEmail'     => (string) mconfig('paypal_email'),
            'paypalTitle'     => (string) mconfig('paypal_title'),
            'paypalCurrency'  => (string) mconfig('paypal_currency'),
            'donationText'    => lang('donation_txt_2'),
            'returnUrl'       => (string) mconfig('paypal_return_url'),
            'notifyUrl'       => (string) mconfig('paypal_notify_url'),
            'customUserId'    => (string) ($_SESSION['userid'] ?? ''),
        ]);
    }
}

